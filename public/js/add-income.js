$(document).ready(function () {
  $("form").submit(function (event) {
    event.preventDefault();
    var amount = $("#amount").val();
    var category = $("#category").val();
    var date = $("#date").val();
    var comment = $("#comment").val();

    $.post("income/add", {
      amount: amount,
      category: category,
      date: date,
      comment: comment,
    })
      .done(function (data, status) {
        $("#messages").html(data);
        console.log(status);
      })
      .fail(function (status) {
        console.log(status);
      });
    $("#amount, #category, #date, #comment").val(null);
  });
});
