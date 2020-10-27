jQuery(document).ready(function ($) {
  $("#settings-list").accordionjs({
    openSection: function (section) {
      $(".acc_section.acc_active > .acc_head > i").removeClass("fa-angle-down");
      $(".acc_section.acc_active > .acc_head > i").addClass("fa-angle-up");
    },
    beforeOpenSection: function (section) {
      $(".acc_section > .acc_head > i").removeClass("fa-angle-up");
      $(".acc_section > .acc_head > i").addClass("fa-angle-down");
    },
  });
});
