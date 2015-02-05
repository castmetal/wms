//JavaScript
$(document).ready(function(){
    $(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
    
    /***************************************
    Dialog Ajax windows
    ***************************************/ 
    $('.dialogIframe').click(function (ev, el) {
        //stop event    
        ev.preventDefault();
        //load window
        $.wmsDialogFrame({
            'width':800, 
            'height':500, 
            'url': this.href, 
            'title':$(this).html()
        });
    });
    
    $('.dialogAjax').click(function (ev, el) {
        //stop event    
        ev.preventDefault();
        //load window
        $.wmsDialogAjax({
            'width':800, 
            'height':500, 
            'url': this.href, 
            'title':$(this).html()
        });
    });    
    

    /* Brazilian initialisation for the jQuery UI date picker plugin. */
    /* Written by Leonildo Costa Silva (leocsilva@gmail.com). */
    jQuery(function($){
        $.datepicker.regional['pt-BR'] = {
            closeText: 'Fechar',
            prevText: '&#x3c;Anterior',
            nextText: 'Pr&oacute;ximo&#x3e;',
            currentText: 'Hoje',
            monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
            'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
            monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
            'Jul','Ago','Set','Out','Nov','Dez'],
            dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','S&aacute;bado'],
            dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
            dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 0,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $.datepicker.setDefaults($.datepicker.regional['pt-BR']);
    });

    $('input.money').priceFormat({
        prefix: '',
        centsSeparator: ',',
        thousandsSeparator: '.'
    });
    
    /***************************************
    Botoes
    ***************************************/
    /** 
     * Caixa de diálogo de exclusão
     */
    //Confirmação de exclusão de registros
    $('.btnDelete, a.del, a.delete').click(function() {
        targetUrl = $(this).attr("href");
        // a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
        $( "#dialog:ui-dialog" ).dialog( "destroy" );

        $( "#dialog-delete-record" ).dialog({
            resizable: false,
            height:160,
            modal: true,
            buttons: {
                "Deletar registro": function() {
                    window.location.href = targetUrl;
                //$( this ).dialog( "close" );
                },
                'Cancelar' : function() {
                    $( this ).dialog( "close" );
                }
            }
        });

        return false;
    });
    

    /***************************************
        Forms
    ***************************************/

    // masks
    $('input:text').setMask();
    
    // focus
    $('.focus').focus();

    // button save
    $('.btnSave').click(function() {
        $('.saveForm').submit();
    });
    
    // Save Form
   // $(window).keypress(function(event) {
   //     if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19)) return true;
    //    $('.saveForm').submit();
    //   event.preventDefault();
     //   return false;
    //}); 
    
    var ctrl_down = false;
    var ctrl_key = 17;
    var s_key = 83;

    $(document).keydown(function(e) {
        if (e.keyCode == ctrl_key) ctrl_down = true;
    }).keyup(function(e) {
        if (e.keyCode == ctrl_key) ctrl_down = false;
    });

    $(document).keydown(function(e) {
        if (ctrl_down && (e.keyCode == s_key)) {
            $('.saveForm').submit();
            e.preventDefault();
            return false;
        }
    });
    

    $('.gPagerFormSelect').change(function() {
        $(location).attr('href',$(this).val());
    });

    // button to go back
    $('.btnBack').click(function () {
        window.history.back();
    });

    // date
    $("input.date").datepicker({
        dateFormat: 'dd/mm/yy'
    //showOn: "button",
    //buttonImage: ADMIN_URL + "../img/icons/calendar.png",
    //buttonImageOnly: true
    });
    
    //Confirmação de uma operação qualquer
    $('.btnConfirm, a.confirm').click(function() {
        return confirm('Tem certeza que deseja executar essa ação?') ? true : false;
    });

    //Fechar as mensagens
    $('a.fmBtnClose').click(function() {
        // remove li
        $(this).parent('div').parent('li').fadeOut();
    });
    
    //Mudança de depósito logado
    $('#idDepositoLogado').change(function(){
        if ($(this).val() != 0) {
            window.location = URL_MODULO + '/deposito/mudar-deposito-logado/id/' + $(this).val();
        }
    });
    
    /***************************************
    Menu
    ***************************************/
    var options = {
        arrowSrc: URL_SISTEMA + '/img/jquery/menu/arrow_right.png'
    };
    $('.menu').clickMenu(options);

    var checks = $('.check-all').parents('.grid').find(':checkbox[class!=check-all]');

    $('.check-all').click(function () {
        checks.attr('checked', this.checked);
        $('.gMassAction .check-selected-counter').text((this.checked) ? checks.length : 0);
    });

    $(checks).click(function(){
        counterEl = $('.gMassAction .check-selected-counter');
        counterVal = counterEl.text();		
                
        if (this.checked) {
            counterVal++;
            counterEl.text(counterVal);
        } else {
            counterVal--;
            counterEl.text(counterVal);
        }
    });

    $('.grid .massaction-button').click(function(){
        var values = new Array();
        var checked = $('.check-all').parents('.grid').find(':checkbox[class!=check-all][checked=true]');

        $.each(checked, function (index, value){
            values[index] = $(value).val();
        });

        $('.massaction-values').val(values.join(','));
        $('.massaction-form').submit();
    });
    
    // converte digitacao no sistema para maiusculo
    $('input.upper').Setcase({
        caseValue: 'upper'
    });
    $('input.lower').Setcase({
        caseValue: 'lower'
    });
    $('textarea.upper').Setcase({
        caseValue: 'upper'
    });
    $('textarea.lower').Setcase({
        caseValue: 'lower'
    });
    
    /***************************************
     JMVC plugins
    ***************************************/
    $('#acesso-perfil-form').perfilUsuario();
    $('#pessoa-dados-pessoais').pessoaDadosPessoais();
    $('#pessoa-endereco').pessoaEndereco();
    $('#pessoa-telefone').pessoaTelefone();
    $('#produto-embalagem').produtoEmbalagem();
    $('#produto-volume').produtoVolume();
    $('#produto-dado-logistico').produtoDadoLogistico();
    $('#produto-form').produto();
    $('.calcular-medidas').calculaMedidas();
    $('#btn-ajuda').ajuda();
    $('.box').box();
    $('#grid-auditoria').auditoria();
    $('#deposito-endereco-form').depositoEndereco();
    $('#grid-veiculo').veiculo();
    $('#sistema-menu-form').menuItem();
    $('#filtro-nota-fiscal').filtroNotaFiscal();
    $('#recebimento-divergencia-form, #form-recebimento-conferencia, #recebimento-index-grid').recebimento();
    $('#filtro-expedicao-mercadoria-form').expedicao();
    $('#enderecamento-form, #deposito-endereco-filtro-form, #cadastro-movimentacao, .exportar-saldo-csv ').enderecamento();
});