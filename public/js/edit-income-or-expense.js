$(document).ready(function () {
  // Usuń alerty i komunikaty o błędach
  function deleteMessages() {
    setTimeout(function () {
      $(".alert").remove();
      $(".errors").remove();
    }, 4000);
  }

  // Przeładuj tabele po edycji pojedynczego przychodu/wydatku
  function reloadTables() {
    $.get("balance/autoload", function (data, status) {
      $("#autoloadTables").html(data);
    });
  }

  // Usuń przychód/wydatek
  function deleteIncomeOrExpense(idToDelete, tableName, whatMessages) {
    $("#send-delete").off("submit");
    $("#send-delete").submit(function (event) {
      event.preventDefault();

      $("#deleteModal").modal("hide");

      $.post("balance/delete", {
        id_to_delete: idToDelete,
        table_name: tableName,
        what_messages: whatMessages,
      })
        .done(function (data, status) {
          reloadTables();
          $("#" + whatMessages + "-messages").html(data);
          deleteMessages();
          $("#send-delete").off("submit");
        })
        .fail(function (status) {
          console.log(status);
        });
    });
  }

  // Edytuj/usuń pojedynczy przychód
  $(".edit-income").submit(function (event) {
    event.preventDefault();
    $("#send-edit-income").off("submit");

    var category = event.target[1].defaultValue;
    var date = event.target[2].defaultValue;
    var amount = event.target[3].defaultValue;
    var comment = event.target[4].defaultValue;
    var incomeId = event.target[5].defaultValue;

    $("#income-category-edit")
      .children()
      .each(function () {
        if ($(this).val() == category) $(this).prop("selected", true);
      });

    $("#income-id-edit").val(incomeId);
    $("#income-date-edit").val(date);
    $("#income-amount-edit").val(amount);
    $("#income-comment-edit").val(comment);

    $("#send-edit-income").submit(function (event) {
      event.preventDefault();

      $("#editIncomeModal").modal("hide");

      $.post("balance/edit-income", {
        income_id: incomeId,
        category: $("#income-category-edit").val(),
        date: $("#income-date-edit").val(),
        amount: $("#income-amount-edit").val(),
        comment: $("#income-comment-edit").val(),
      })
        .done(function (data, status) {
          reloadTables();
          $("#incomes-messages").html(data);
          deleteMessages();
          $("#send-edit-income").off("submit");
        })
        .fail(function (status) {
          console.log(status);
        });
    });
  });
  $(".delete-income").click(function (event) {
    var idToDelete = event.currentTarget.parentElement[5].defaultValue;
    deleteIncomeOrExpense(idToDelete, "incomes", "incomes");
  });

  // Edytuj/usuń wydatek
  $(".edit-expense").submit(function (event) {
    event.preventDefault();

    $("#send-edit-expense").off("submit");

    var category = event.target[1].defaultValue;
    var paymentMethod = event.target[3].defaultValue;
    var date = event.target[4].defaultValue;
    var amount = event.target[5].defaultValue;
    var comment = event.target[6].defaultValue;
    var expenseId = event.target[7].defaultValue;

    $("#expense-category-edit")
      .children()
      .each(function () {
        if ($(this).val() == category) $(this).prop("selected", true);
      });
    $("#payment-method-edit")
      .children()
      .each(function () {
        if ($(this).val() == paymentMethod) $(this).prop("selected", true);
      });

    $("#expense-id-edit").val(expenseId);
    $("#expense-date-edit").val(date);
    $("#expense-amount-edit").val(amount);
    $("#expense-comment-edit").val(comment);

    $("#send-edit-expense").submit(function (event) {
      event.preventDefault();

      $("#editExpenseModal").modal("hide");

      $.post("balance/edit-expense", {
        expense_id: expenseId,
        category: $("#expense-category-edit").val(),
        payment_method: $("#payment-method-edit").val(),
        date: $("#expense-date-edit").val(),
        amount: $("#expense-amount-edit").val(),
        comment: $("#expense-comment-edit").val(),
      })
        .done(function (data, status) {
          reloadTables();
          $("#expenses-messages").html(data);
          $("#send-edit-expense").off("submit");
          deleteMessages();
          console.log(status);
        })
        .fail(function (status) {
          console.log(status);
        });
    });
  });
  $(".delete-expense").click(function (event) {
    var idToDelete = event.currentTarget.parentElement[7].defaultValue;
    deleteIncomeOrExpense(idToDelete, "expenses", "expenses");
  });
});
