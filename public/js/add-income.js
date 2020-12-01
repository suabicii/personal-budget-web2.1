$(document).ready(function () {
  // Domyślna data w formularzu - dzisiejsza
  var date = new Date();
  var month =
    date.getMonth() < 10 ? "0" + date.getMonth() : date.getMonth() + 1;
  var day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();

  $("#date").val(date.getFullYear() + "-" + month + "-" + day);

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
        setTimeout(function () {
          $(".alert").remove();
          $(".errors").remove();
        }, 4000);
        console.log(status);
      })
      .fail(function (status) {
        console.log(status);
      });
    $("#amount, #category, #date, #comment").val(null);
  });
});
