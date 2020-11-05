$(document).ready(function () {
  $("form").submit(function (event) {
    event.preventDefault();
    var payment = $("#payment").val();
    var amount = $("#amount").val();
    var category = $("#category").val();
    var date = $("#date").val();
    var comment = $("#comment").val();

    $.post("expense/add", {
      payment: payment,
      amount: amount,
      category: category,
      date: date,
      comment: comment,
    })
      .done(function (data, status) {
        $("#messages").html(data);
        setTimeout(function () {
          $(".alert").remove();
          $(".errors").remove();
        }, 4000);
        console.log(status);
      })
      .fail(function (status) {
        console.log(status);
      });
    $("#payment, #amount, #category, #date, #comment").val(null);
  });
});
