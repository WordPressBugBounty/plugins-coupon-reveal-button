jQuery(document).ready(function ($) {
  const text_element = $(".reveal__button__text");
  const hidden_element = $(".reveal__button__hidden__content");
  setTimeout(function () {
    spbcta_updateWidth();
  }, 1000);

  $("#colorSelect-bg").change(function () {
    text_element[0].style.removeProperty("background");
    text_element[0].style.setProperty("background", $(this).val(), "important");
  });
  $("#colorSelect-f").change(function () {
    text_element[0].style.removeProperty("color");
    text_element[0].style.setProperty("color", $(this).val(), "important");
  });
  $("#colorSelect-hf").change(function () {
    hidden_element[0].style.removeProperty("color");
    hidden_element[0].style.setProperty("color", $(this).val(), "important");
  });
  $("#colorSelect-hbg").change(function () {
    hidden_element[0].style.removeProperty("background-color");
    hidden_element[0].style.setProperty(
      "background-color",
      $(this).val(),
      "important"
    );
  });
  $("#colorSelect-bor").change(function () {
    hidden_element[0].style.removeProperty("border-color");
    hidden_element[0].style.setProperty(
      "border-color",
      $(this).val(),
      "important"
    );
  });

  $("input[name='CTAutext']").change(function () {
    $(".reveal__button__text").text($(this).val());
    spbcta_updateWidth();
  });
  $("input[name='CTAureveal']").change(function () {
    spbcta_updatereveal();
  });

  function spbcta_updatereveal() {
    /////// hide reveal
    if (!$("input[name='CTAureveal']").val()) {
      $(".reveal__button__hidden__content").css("display", "none");
    } else {
      $(".reveal__button__hidden__content").css("display", "inline");
    }
    ////////
    if ($("input[name='CTAureveal']").val() != null) {
      var str = $("input[name='CTAureveal']").val();
      var n = 3;
      if (str.length <= 3) {
        n = 2;
      }
      var hidstr = str.slice(str.length - n);
      $(".reveal__button__hidden__content").text(hidstr);
      $(".reveal__button__link").attr(
        "onClick",
        "spbctaNM.func.spbcta_pass('" +
          btoa(unescape(encodeURIComponent(str))) +
          '\',this,"#previewlink",0,true)'
      );
      spbcta_updateWidth();
    }
  }

  function spbcta_updateWidth() {
    /* $(".reveal__button__hidden__content").css({
      "min-width": $(".reveal__button__text").width() + "px",
    }); */
    $(".reveal__button__wrapper").removeClass("reveal__button__content");
    $(".reveal__button__link").removeClass("spbcta_selectable");
    $(window).resize();
  }

  spbcta_updatereveal();

  if ($("input[name='CTABlank']").val() == 1) {
    $("#spbcta_blank").prop("checked", true);
  }
  if ($("input[name='CTAnofollow']").val() == 1) {
    $("#spbcta_nofollow").prop("checked", true);
  }

  $("#spbcta_blank").on("change", function () {
    if ($(this).is(":checked")) {
      $("input[name='CTABlank']").val(1);
    } else {
      $("input[name='CTABlank']").val(0);
    }
  });

  $("#spbcta_nofollow").on("change", function () {
    if ($(this).is(":checked")) {
      $("input[name='CTAnofollow']").val(1);
    } else {
      $("input[name='CTAnofollow']").val(0);
    }
  });

  $(".spbcta_btn.delete_btn").click(function (e) {
    e.preventDefault();
    var target = $(this).attr("href");
    spbcta_confirm(
      "Delete Reveal Button",
      "Are you sure you want to delete this button?",
      "Delete",
      "Cancel",
      function () {
        window.location.href = target;
      }
    );
  });

  $(".spbcta_shortcode").click(function () {
    this.setSelectionRange(0, this.value.length);
  });

  $(".spbcta_success").fadeOut(10000, function () {
    $(this).remove();
  });

  var resetready = false;
  $("#spbcta-preview-reset").click(function () {
    resetready = false;
    spbcta_updateReset_btn();
    spbcta_updatereveal();
  });
  $(".reveal__button__link").click(function () {
    resetready = true;
    spbcta_updateReset_btn();
  });
  function spbcta_updateReset_btn() {
    if (!resetready) {
      $("#spbcta-preview-reset").css("background-color", "#DEDEDE");
    } else {
      $("#spbcta-preview-reset").css("background-color", "");
    }
  }

  function spbcta_confirm(title, msg, $true, $false, callback) {
    var $content =
      "<div class='spbcta_dialog-ovelay'>" +
      "<div class='spbcta_dialog'><header>" +
      " <h3> " +
      title +
      " </h3> " +
      "<i class='spbcta_fa spbcta_fa-close'></i>" +
      "</header>" +
      "<div class='spbcta_dialog-msg'>" +
      " <p> " +
      msg +
      " </p> " +
      "</div>" +
      "<footer>" +
      "<div class='spbcta_controls'>" +
      " <button class='spbcta_button spbcta_button-danger doAction'>" +
      $true +
      "</button> " +
      " <button class='spbcta_button spbcta_button-default cancelAction'>" +
      $false +
      "</button> " +
      "</div>" +
      "</footer>" +
      "</div>" +
      "</div>";
    $("#wpbody").prepend($content);
    $(".doAction").click(function () {
      $(this)
        .parents(".spbcta_dialog-ovelay")
        .fadeOut(500, function () {
          $(this).remove();
        });
      callback();
      return true;
    });
    $(".cancelAction, .fa-close").click(function () {
      $(this)
        .parents(".spbcta_dialog-ovelay")
        .fadeOut(500, function () {
          $(this).remove();
        });
      return false;
    });
  }
});
