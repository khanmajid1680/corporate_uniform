$(document).ready(function () {});
const set_style_setting_field = (id) => {
  const path = `master/style_setting/handler`;
  const form_data = { func: "get_data", id };
  ajaxCall(
    "POST",
    path,
    form_data,
    "JSON",
    (resp) => {
      if (handle_response(resp)) {
        const { data, msg } = resp;
        if (data && data.length != 0) {
          $(`#style_setting_apparel_id`).html(
            `<option value="${data[0][`style_setting_apparel_id`]}">${
              data[0]["apparel_name"]
            }</option>`
          );
          $(`#style_setting_style_id`).html(
            `<option value="${data[0][`style_setting_style_id`]}">${
              data[0]["style_name"]
            }</option>`
          );
          $(`#style_setting_status`).bootstrapToggle(
            data[0][`style_setting_status`] == 1 ? "on" : "off"
          );
          $("#popup_modal_sm").modal("show");
        }
      }
    },
    (errmsg) => {}
  );
};
const style_setting_popup = (args) => {
  const { action = "add", id = 0, field = undefined } = args;
  let title = `<div class="col-12 col-sm-12 col-md-12 col-lg-12">
                  <p class="text-uppercase text-center font-weight-bold">${action} style setting</p>
                </div>`;
  let data = `<form class="form-horizontal" id="style_setting_form" onsubmit="add_update_style_setting(${id}, ${field})">              
                <div class="row pt-1">
                  <div class="col-12">
                    <div class="d-flex flex-wrap form-group floating-form">
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12 floating-label">
                        <p class="text-uppercase">apparel <span class="text-danger">*</span></p>
                        <select
                            class="form-contrl floating-select"
                            id="style_setting_apparel_id"
                            name="style_setting_apparel_id"
                            onkeyup="validate_dropdown(this, ${true})"
                        ></select>
                        <small class="form-text text-muted helper-text" id="style_setting_apparel_id_msg"></small>
                      </div>
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12 floating-label">
                        <p class="text-uppercase">style <span class="text-danger">*</span></p>
                        <select
                            class="form-contrl floating-select"
                            id="style_setting_style_id"
                            name="style_setting_style_id"
                            onkeyup="validate_dropdown(this, ${true})"
                        ></select>
                        <small class="form-text text-muted helper-text" id="style_setting_style_id_msg"></small>
                      </div>
                      ${
                        field == undefined
                          ? `<div class="col-12 col-sm-12 col-md-4 col-lg-4 floating-label">
                              <input 
                                type="checkbox" 
                                id="style_setting_status" 
                                name="style_setting_status" 
                                data-toggle="toggle" 
                                data-on="ACTIVE" 
                                data-off="INACTIVE" 
                                data-onstyle="primary" 
                                data-offstyle="primary" 
                                data-width="100" 
                                data-size="normal" 
                                checked
                              />
                            </div>`
                          : `<input type="hidden" name="style_setting_status" value="1">`
                      }
                    </div>              
                  </div>              
                </div>              
              </form>`;

  let btn = `<button 
              type="button" 
              class="btn btn-sm btn-primary" 
              id="sbt_btn" 
              onclick="add_update_style_setting(${id}, ${field})" 
              style="width:15%;"
              
            >
              <div class="stage d-none"><div class="dot-flashing"></div></div>
              <div class="dot-text text-primary text-uppercase">${action}</div>
            </button>
            <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal">CANCEL</button>`;

  $(".modal-title-sm").html(title);
  $(".modal-body-sm").html(data);
  $(".modal-footer-sm").html(action == "read" ? "" : btn);
  if (id == 0) {
    $("#popup_modal_sm").modal("show");
    $(`#style_setting_status`).bootstrapToggle();
    setTimeout(() => {
      $(`#style_setting_apparel_id`).select2("open");
    }, RELOAD_TIME);
  } else {
    set_style_setting_field(id);
  }

  $("#style_setting_apparel_id").select2(
    select2_default({
      url: `master/apparel/get_select2/_id`,
      placeholder: "SELECT",
      param: true,
    })
  );
  $("#style_setting_style_id").select2(
    select2_default({
      url: `master/style/get_select2/_id`,
      placeholder: "SELECT",
      param: true,
    })
  );
};
const add_update_style_setting = (id, field) => {
  event.preventDefault();
  let check = true;
  notifier(`style_setting_apparel_id`);
  notifier(`style_setting_style_id`);

  if ($(`#style_setting_apparel_id`).val() == null) {
    notifier(`style_setting_apparel_id`, "Required");
    check = false;
  }

  if ($(`#style_setting_style_id`).val() == null) {
    notifier(`style_setting_style_id`, "Required");
    check = false;
  }
  if (!check) {
    toastr.error("You forgot to enter some information.", "Oh snap!!!", {
      closeButton: true,
      progressBar: true,
      preventDuplicates: true,
    });
    $("body, html").animate({ scrollTop: 0 }, 1000);
  } else {
    let path = `master/style_setting/handler`;
    let form_data = $(`#style_setting_form`).serialize();
    form_data += `&func=add_update&id=${id}`;
    ajaxCall(
      "POST",
      path,
      form_data,
      "JSON",
      (resp) => {
        if (handle_response(resp)) {
          const { data, msg } = resp;
          if (id == 0) {
            if (field != undefined) {
              $("#popup_modal_sm").modal("hide");
              // refresh_dropdown_select2(data, field);
            } else {
              $(`#style_setting_apparel_id`).val(null).trigger("change");
              $(`#style_setting_apparel_id`).select2("open");
              $(`#style_setting_style_id`).val(null).trigger("change");
              $(`#style_setting_style_id`).select2("close");

              notifier(`style_setting_apparel_id`);
              notifier(`style_setting_style_id`);
            }
          } else {
            $("#popup_modal_sm").modal("hide");
          }
          toastr.success("", msg, { closeButton: true, progressBar: true });
          $("body, html").animate({ scrollTop: 0 }, 1000);
        }
      },
      (errmsg) => {}
    );
  }
};
const style_setting_remove = (data) => {
  const path = `${link}/${sub_link}/handler`;
  const form_data = { func: "remove", id: data.style_setting_id };
  let html = `<table class="table table-sm table-hover text-uppercase">
                  <tbody>
                      <tr>
                          <td class="font-weight-bold" width="30%" align="right">apparel : </td>
                          <td width="70%">${data.apparel_name}</td>
                      </tr>
                      <tr>
                          <td class="font-weight-bold" width="30%" align="right">style : </td>
                          <td width="70%">${data.style_name}</td>
                      </tr>
                      <tr>
                          <td class="font-weight-bold" width="30%" align="right">status : </td>
                          <td width="70%">
                            ${
                              data.style_setting_status == 1
                                ? "active"
                                : "inactive"
                            }
                          </td>
                      </tr>
                  </tbody>
              </table>`;
  remove_datav3({ path, form_data, html });
};
