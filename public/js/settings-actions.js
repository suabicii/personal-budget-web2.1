$(document).ready(function () {
  // Usuń alerty i komunikaty o błędach
  function deleteMessages() {
    setTimeout(function () {
      $(".alert").remove();
      $(".errors").remove();
    }, 4000);
  }

  // Funkcja edycji kategorii
  function editCategory(
    translatedName,
    originalName,
    tableName,
    route,
    settingsCategory
  ) {
    $("#send-edit").off("submit");
    $("#category-edit").val(translatedName);
    $("#old-name").val(originalName);

    var limit = null;

    // Ujawnij inputa do ustawiania limitów, jeśli użytkownik edytuje
    // kategorię wydatku
    if ($("#is-expense-edited").val() == "true") {
      $("#limit").removeClass("d-none");
      $("#limit").addClass("d-block");

      $("#limit-on").change(function () {
        if ($("#limit-on").prop("checked")) {
          $("#limit-amount").prop("disabled", false);
          $.get("settings/get-limit", {
            category: originalName,
          })
            .done(function (data, status) {
              $("#limit-fetched").html(data);
              $("#limit-amount").val($("#amount-limit-fetched").val());
            })
            .fail(function (status) {
              console.log(status);
            });
        } else {
          $("#limit-amount").prop("disabled", true);
          $("#limit-amount").val(null);
        }
      });
    } else {
      $("#limit").addClass("d-none");
      $("#limit").removeClass("d-block");
    }

    $("#send-edit").submit(function (event) {
      event.preventDefault();

      $("#editModal").modal("hide");

      if ($("#limit-amount").val() == "") {
        limit = null;
      } else {
        limit = $("#limit-amount").val();
      }

      $.post("settings/edit", {
        table_name: tableName,
        old_name: originalName,
        new_name: $("#category-edit").val(),
        limit: limit,
      })
        .done(function (data, status) {
          $(".messages-finances").html(data);
          deleteMessages();
          getTable(route, settingsCategory, tableName);
          $("#send-edit").off("submit");
        })
        .fail(function (status) {
          console.log(status);
        });
    });
  }

  // Dodawanie kategorii
  function addCategory(tableName, route, settingsCategory) {
    $("#send-add").off("submit");
    $("#send-add").submit(function (event) {
      event.preventDefault();
      var category = $("#category-add").val();

      $("#addModal").modal("hide");

      $.post("settings/add", {
        category: category,
        table_name: tableName,
      })
        .done(function (data, status) {
          $(".messages-finances").html(data);
          deleteMessages();
          getTable(route, settingsCategory, tableName);
          $("#category-add").val("");
          $("#send-add").off("submit");
        })
        .fail(function (status) {
          console.log(status);
        });
    });
  }

  // Usuwanie kategorii
  function deleteCategory(
    translatedName,
    originalName,
    tableName,
    route,
    settingsCategory
  ) {
    $("#send-delete").off("submit");
    $("#category-to-delete").html(translatedName);
    $("#original-category-to-delete").val(originalName);

    $("#send-delete").submit(function (event) {
      event.preventDefault();

      $("#deleteModal").modal("hide");

      $.post("settings/delete", {
        category: originalName,
        table_name: tableName,
      })
        .done(function (data, status) {
          $(".messages-finances").html(data);
          getTable(route, settingsCategory, tableName);
          deleteMessages();
          $("#send-delete").off("submit");
        })
        .fail(function (status) {
          console.log(status);
        });
    });
  }

  // Pobierz kategorie do tabeli
  function getTable(route, settingsCategory, backEndTable) {
    $.get("settings/get-" + route)
      .done(function (data, status) {
        $("#" + route).html(data);

        // Edycja/dodawanie/usuwanie kategorii
        $(".edit-" + settingsCategory).submit(function (event) {
          event.preventDefault();
          if (settingsCategory == "expense") {
            $("#is-expense-edited").val("true");
          } else {
            $("#is-expense-edited").val("false");
          }
          var translatedName = event.target[0].defaultValue;
          var originalName = event.target[1].defaultValue;
          editCategory(
            translatedName,
            originalName,
            backEndTable,
            route,
            settingsCategory
          );
        });
        $(".add-" + settingsCategory).click(function () {
          addCategory(backEndTable, route, settingsCategory);
        });
        $(".delete-" + settingsCategory).click(function (event) {
          deleteCategory(
            event.currentTarget.dataset.translatedName,
            event.currentTarget.dataset.originalName,
            backEndTable,
            route,
            settingsCategory
          );
        });
      })
      .fail(function (status) {
        console.log(status);
      });
  }

  getTable(
    "incomes-categories",
    "income",
    "incomes_category_assigned_to_users"
  );
  getTable(
    "expenses-categories",
    "expense",
    "expenses_category_assigned_to_users"
  );
  getTable("payment-methods", "payment", "payment_methods_assigned_to_users");

  // Zmiana danych użytkownika
  $("#change-data-form").submit(function (event) {
    event.preventDefault();
    var username = $("#username").val();
    var email = $("#email").val();
    var firstName = $("#name").val();
    var oldPassword = $("#old-password").val();
    var newPassword = $("#new-password").val();
    var newPasswordConfirmation = $("#new-password-confirmation").val();
    $.post("settings/change-user-data", {
      username: username,
      email: email,
      name: firstName,
      old_password: oldPassword,
      new_password: newPassword,
      new_password_confirmation: newPasswordConfirmation,
    })
      .done(function (data, status) {
        $("#messages").html(data);
        deleteMessages();
      })
      .fail(function (status) {
        console.log(status);
      });
    $(
      "#username, #email, #name, #old-password, #new-password, #new-password-confirmation"
    ).val("");
  });
});
