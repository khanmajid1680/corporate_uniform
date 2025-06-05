$(document).ready(function () {
  $(`#jim_proces_id`).select2(
    select2_default({
      url: `${link}/${sub_link}/get_select2/_proces_id`,
      placeholder: "select",
    })
  );
  $(`#jim_karigar_id`).select2(
    select2_default({
      url: `${link}/${sub_link}/get_select2/_karigar_id`,
      placeholder: "select",
      param: () => $("#jim_proces_id").val(),
    })
  );
  $(`#obt_id`)
    .select2(
      select2_default({
        url: `${link}/${sub_link}/get_select2/_obt_id`,
        placeholder: "select",
        barcode: "obt_id",
      })
    )
    .on("change", (event) => get_barcode_data(event.target.value));
});

let trans_data = [];
const get_transaction = () => {
  if (["edit", "read"].includes(get_url_string("action"))) {
    let id = get_url_string("id");
    if (id) {
      const path = `${link}/${sub_link}/handler`;
      const form_data = { func: "get_transaction", id };
      ajaxCall(
        "POST",
        path,
        form_data,
        "JSON",
        (resp) => {
          if (handle_response(resp)) {
            const { data, msg } = resp;
            if (data && data.length != 0) {
              trans_data = data;
              let result = paginate(trans_data, page);
              if (result && result.length != 0) {
                result.forEach((value) => add_wrapper_data(value, true));
              }
            }
            $("#transaction_count").html(trans_data.length);
          }
        },
        (errmsg) => {}
      );
    }
  }
};
const get_barcode_data = (obt_id) => {
  if (!obt_id) return false;
  const index = trans_data.findIndex((value) => value.obt_id == obt_id);
  if (index >= 0) {
    toastr.error("Barcode already added !!!", "", {
      closeButton: true,
      progressBar: true,
      preventDuplicates: true,
    });
    return false;
  }
  const path = `${link}/${sub_link}/handler`;
  let form_data = `func=get_barcode_data&id=${obt_id}`;
  ajaxCall(
    "POST",
    path,
    form_data,
    "JSON",
    (resp) => {
      if (handle_response(resp)) {
        const { data, msg } = resp;
        if (data && data.length != 0) {
          data.forEach((value) => add_wrapper_data(value));
          $("#transaction_count").html(trans_data.length);
          toastr.success(`${data[0]["obt_item_code"]}`, msg, {
            closeButton: true,
            progressBar: true,
            preventDuplicates: true,
          });
        }
      }
      $("#obt_id").val(null).trigger("change");
    },
    (errmsg) => {
      $("#obt_id").val(null).trigger("change");
    }
  );
};
const add_wrapper_data = (data) => {
  const {
    obt_id,
    obt_item_code,
    apparel_name,
    customer_name,
    entry_no,
    entry_date,
    trial_date,
    delivery_date,
    isExist = false,
  } = data;
  let tr = `<tr id="row_${obt_id}">
              <td >${obt_item_code}</td>
              <td >${apparel_name}</td>
              <td >${customer_name}</td>
              <td >${entry_no}</td>
              <td >${entry_date}</td>
              <td >${trial_date}</td>
              <td >${delivery_date}</td>
              <td >
                ${
                  isExist
                    ? `<button 
                        type="button" 
                        class="btn btn-sm"
                        ><i class="text-danger fa fa-ban"></i></button>`
                    : `<a 
                        type="button" 
                        class="btn btn-sm btn-primary" 
                        onclick="remove_transaction(${obt_id})"
                        ><i class="text-danger fa fa-trash"></i></a>`
                }
              </td>
            </tr>`;
  $("#transaction_wrapper").append(tr);
  trans_data.push(data);
};
const remove_transaction = (obt_id) => {
  const find = trans_data.find((value) => value.obt_id == obt_id);
  trans_data = trans_data.filter((value) => value.obt_id != obt_id);
  toastr.info(`${find["obt_item_code"]}`, "BARCODE REMOVED FROM LIST.", {
    closeButton: true,
    progressBar: true,
  });
  $(`#row_${obt_id}`).detach();
  $("#transaction_count").html(trans_data.length);
};
const add_edit = () => {
  event.preventDefault();
  let check = true;
  if ($(`#jim_proces_id`).val() == null) {
    notifier(`jim_proces_id`, "Required");
    check = false;
  }
  if ($(`#jim_karigar_id`).val() == null) {
    notifier(`jim_karigar_id`, "Required");
    check = false;
  }
  if (trans_data.length == 0) {
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
    const path = `${link}/${sub_link}/handler`;
    let form_data = $(`#_form`).serialize();
    form_data += `&func=add_edit`;
    form_data += `&trans_data=${JSON.stringify(trans_data)}`;
    ajaxCall(
      "POST",
      path,
      form_data,
      "JSON",
      (resp) => {
        const { data, msg } = resp;
        if (handle_response(resp)) {
          toastr.success("", msg, {
            closeButton: true,
            progressBar: true,
            preventDuplicates: true,
          });
          setTimeout(() => {
            window.location.reload();
          }, RELOAD_TIME);
        }
      },
      (errmsg) => {}
    );
  }
};
