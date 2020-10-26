function getTable(route) {
  $.get(route)
    .done(function (data, status) {
      $("#tables").html(data);
      $.get("/js/balance.js");
    })
    .fail(function (status) {
      console.log(status);
    });
}
// Pobieranie tabel
$(document).ready(function () {
  $("#general-view").click(function (event) {
    event.preventDefault();
    getTable("balance/general");
  });
  $("#particular-view").click(function (event) {
    event.preventDefault();
    getTable("balance/particular");
  });

  $("#current-month").click(function (event) {
    event.preventDefault();
    getTable("balance/current-month");
  });
  $("#previous-month").click(function (event) {
    event.preventDefault();
    getTable("balance/previous-month");
  });
  $("#current-year").click(function (event) {
    event.preventDefault();
    getTable("balance/current-year");
  });
  $("#custom-date-choice").submit(function (event) {
    event.preventDefault();
    var startDate = $("#start-date").val();
    var endDate = $("#end-date").val();
    $.post("balance/custom-date", {
      start_date: startDate,
      end_date: endDate,
    })
      .done(function (data, status) {
        $("#tables").html(data);
        $.get("/js/balance.js");
      })
      .fail(function (status) {
        console.log(status);
      });
    $("#customDateModal").modal("hide");
  });

  // Pobieranie tabel po odświeżeniu strony
  $.get("balance/autoload", function (data, status) {
    $("#autoloadTables").html(data);
  });
});
