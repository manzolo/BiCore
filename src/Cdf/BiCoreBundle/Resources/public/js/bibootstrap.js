$(document).ready(function () {
    bootbox.setDefaults({
        locale: "it"
    });
});

function openloaderspinner()
{
    $("#spinnerloader").addClass("is-active");
}
function closeloaderspinner()
{
    $("#spinnerloader").removeClass("is-active");
}
function formlabeladjust()
{
    $('.form-group label').each(function (index, object) {
        var fieldtowakeup = $(object).attr("for");
        if ($("#" + fieldtowakeup).val() || $("#" + fieldtowakeup).is('select')) {
            $(object).addClass("active");
        }
    });
    $(function () {
        $('.bidatepicker').datetimepicker({
            locale: 'it',
            format: 'L'
        });
        $('.bidatetimepicker').datetimepicker({
            locale: 'it'
        });
        
        //Per impostare il layout delle select come bootstrapitalia
        $(".bootstrap-select-wrapper select").selectpicker('refresh');

    });
}
