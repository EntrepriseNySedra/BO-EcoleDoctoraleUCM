( function( $ ) {
    $(document ).ready( function() {
        $( document ).ajaxStart(function() {
            $('#ajaxspinner').removeClass('hidden');
        });
        /* hide spinner */
        $( document ).ajaxStop(function() {
            $('#ajaxspinner').addClass('hidden');
        });
        $('#offers_offerType').change(function () {
            let $val = $(this).val();;
            if ($val != 1) {
                $('#bloc_type').addClass('hidden');
            } else {
                $('#bloc_type').removeClass('hidden');
            }
        });
        $("#actualite_ressourceType, #article_ressourceType").on("change",function(){
            var $this       = $(this);
            display_list_type($this.val());
        });
        if($("#rubrique_parent").val()=="" || $("#actualite_resourceUuid").val()=="" || $("#article_resourceUuid").val()==""){
            var parentFirst = $("#rubrique_ressourceType").first().val();
            $("input[name='rubrique[parent]']").val(parentFirst);
            $("input[name='actualite[resourceUuid]']").val(parentFirst);
            $("input[name='article[resourceUuid]']").val(parentFirst);
        }
    });

    $(document).on('change', '#offers_region, #offers_departement', function (evt) {
        let $field = $(this);
        let $regionField = $('#offers_region');
        let target = '#' + $field.attr('id').replace('departement', 'cities').replace('region', 'departement');
        let $form = $field.closest('form');
        let $data = {};
        $data[$regionField.attr('name')] = $regionField.val();
        $data[$field.attr('name')] = $field.val();
        $.post($form.attr('action'), $data).then(function (data) {
            let $input = $(data).find(target);
            $(target).replaceWith($input);
        });
    })

    $(document).on('change', '#rubrique_ressourceType', function (evt) {
        let $field = $(this);
        $("input[name='rubrique[parent]']").val($field.val());
        $("input[name='actualite[resourceUuid]']").val($field.val());
        $("input[name='article[resourceUuid]']").val($field.val());
    })

    $(document).on('change', '#departement_ressourceType', function (evt) {
        let $field = $(this);
        $("input[name='departement[parent]']").val($field.val());
        $("input[name='actualite[resourceUuid]']").val($field.val());
        $("input[name='article[resourceUuid]']").val($field.val());
    })

    $(document).on('change', '#mention_ressourceType', function (evt) {
        let $field = $(this);
        $("input[name='mention[parent]']").val($field.val());
        $("input[name='actualite[resourceUuid]']").val($field.val());
        $("input[name='article[resourceUuid]']").val($field.val());
    })

    $(document).on('change', '#niveau_ressourceType', function (evt) {
        let $field = $(this);
        $("input[name='niveau[parent]']").val($field.val());
        $("input[name='actualite[resourceUuid]']").val($field.val());
        $("input[name='article[resourceUuid]']").val($field.val());
    })

    $(document).on('click', '#checkbox', function (evt) {
        let $this = $(this);
        if( $('#checkbox').is(':checked') ){
            $("#rubrique_parent").val(1);
            $("#rubrique_ressourceType").hide();
            $("#rubrique_ressourceType").prev().hide();
        }
        else{
            $("#rubrique_parent").val($("#rubrique_ressourceType").val());
            $("#rubrique_ressourceType").show();
            $("#rubrique_ressourceType").prev().show();
        }
    })

    // Submit form
    $('#rubrique-new,#actualite-new,#article-new').submit(function() {
        $("#rubrique_ressourceType").remove();
        $("#departement_ressourceType").remove();
        $("#mention_ressourceType").remove();
        $("#niveau_ressourceType").remove();
    });
    
    //Début user -> mention
    // $(document).on('change', '#registration_form_profil', function (evt) {
    //     if( $("#registration_form_profil").val() === "5"){
    //         $(".div-mentions").show();
    //     }
    //     else{
    //         $(".div-mentions").hide();
    //     }
    // });
    
    $(document).on('click', '#btn-submit-product-form', function (evt) {
        $("#agency-new").submit();
    });
    //Fin user -> mention

} )( jQuery );


function display_list_type(elt) {
    elt = elt.toLowerCase();
    $(".rubrique-group").attr("style","display:none");
    $(".departement-group").attr("style","display:none");
    $(".mention-group").attr("style","display:none");
    $(".niveau-group").attr("style","display:none");
    $("."+elt+"-group").attr("style","display:block");
    var parentFirst = $("#"+elt+"_ressourceType").first().val();
    $("#actualite_resourceUuid, #article_resourceUuid").val(parentFirst);
}