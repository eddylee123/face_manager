define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {

        },
        sel_data: function () {
            $('.click').click(function(){

            });
            $('.area_hide').click(function(){
                var obj = $('.area');
                if(obj.css('display')=='none'){
                    obj.show();
                    $(this).val('隐藏');
                }
                else{
                    obj.hide();
                    $(this).val('显示');
                }
            });
        },
    };

    return Controller;
});
