$(document).ready(function () {
  // Domyślna data w formularzu - dzisiejsza
  var date = new Date();
  var month =
    date.getMonth() < 10 ? "0" + date.getMonth() : date.getMonth() + 1;
  var day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

  $("#date").val(date.getFullYear() + "-" + month + "-" + day);

  // Usuń alerty i komunikaty o błędach
  function deleteMessages() {
    setTimeout(function () {
      $(".alert").remove();
      $(".errors").remove();
    }, 4000);
  }

  $("#add-expense").submit(function (event) {
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
        deleteMessages();
        console.log(status);
      })
      .fail(function (status) {
        console.log(status);
      });
    $("#payment, #amount, #category, #date, #comment").val(null);
  });
});
