define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wage_pay/index' + location.search,
                    add_url: 'wage_pay/add',
                    edit_url: 'wage_pay/edit',
                    del_url: 'wage_pay/del',
                    multi_url: 'wage_pay/multi',
                    import_url: 'wage_pay/import',
                    table: 'wage_pay',
                }
            });

            var table = $("#table");

            //  e.preventDefault();
            //var ids = Table.api.selectedids(table);
            /*
            Config.columns
            Config.moduleurl
            Config.controllername
               , formatter: function(val, row){
               var html = url_class_val(row.id+'" datas="status=1' , "btn btn-xs btn-success btn-editone" , '<i class="fa fa-pencil"></i>');
               return html;
               }}
               */
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    // ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        // {checkbox: true},
                        {field: 'operate', title: __('Operate'), table: table,
                            buttons:
                                [
                                    {
                                        name: 'detail',
                                        text:__('详情'),
                                        title: __('补贴扣费详情'),
                                        //图标
                                        icon: 'fa fa-file',
                                        //btn-dialog表示为弹窗
                                        classname: 'btn btn-xs btn-primary btn-dialog',
                                        //弹窗位置，//自带参数ids
                                        url: 'wage_pay/detail',
                                        //弹窗大小
                                        extend: 'data-area=\'["70%","70%"]\'',
                                    },
                                ], operate:false, formatter: Table.api.formatter.buttons,
                        },
                        {field: 'wage_month', title: __('Wage_month'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'org_id', title: __('Org_id'), searchList: Config.org_list, formatter: Table.api.formatter.normal},
                        {field: 'emp_id', title: __('Emp_id'), operate: 'LIKE'},
                        {field: 'emp_name', title: __('Emp_name'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: Config.status_list, formatter: Table.api.formatter.status},
                        {field: 'type', title: __('Type'), searchList: Config.type_list, formatter: Table.api.formatter.normal},
                        {field: 'is_send', title: __('Is_send'), searchList: Config.send_list, formatter: Table.api.formatter.normal},
                        {field: 'pay_able', title: __('Pay_able'), operate: false},
                        {field: 'pay_actual', title: __('Pay_actual'), operate: false},
                        {field: 'process', title: __('Process'), operate: false},
                        {field: 'weekend_day', title: __('Weekend_day'), operate: false},
                        {field: 'official_day', title: __('Official_day'), operate: false},
                        {field: 'need_day', title: __('Need_day'), operate: false},
                        {field: 'work_date', title: __('Work_date'), operate: false},
                        {field: 'work_time', title: __('Work_time'), operate: false},
                        {field: 'over_day', title: __('Over_day'), operate: false},
                        {field: 'turn_day', title: __('Turn_day'), operate: false},
                        {field: 'paid_day', title: __('Paid_day'), operate: false},
                        {field: 'leave_day', title: __('Leave_day'), operate: false},
                        {field: 'night_day', title: __('Night_day'), operate: false},
                        {field: 'mass_score', title: __('Mass_score'), operate: false},
                        {field: 'pay_piece', title: __('Pay_piece'), operate: false},
                        {field: 'pay_piece_sub', title: __('Pay_piece_sub'), operate: false},
                        {field: 'work_piece', title: __('Work_piece'), operate: false},
                        {field: 'pay_time', title: __('Pay_time'), operate: false},
                        {field: 'pay_practice', title: __('Pay_practice'), operate: false},
                        {field: 'pay_over_1', title: __('Pay_over_1'), operate: false},
                        {field: 'pay_over_2', title: __('Pay_over_2'), operate: false},
                        {field: 'pay_weekend', title: __('Pay_weekend'), operate: false},
                        {field: 'pay_official', title: __('Pay_official'), operate: false},
                        {field: 'pay_fixed', title: __('Pay_fixed'), operate: false},
                        {field: 'quality', title: __('Quality'), operate: false},
                        {field: 'pay_clean', title: __('Pay_clean'), operate: false},
                        {field: 'pay_return', title: __('Pay_return'), operate: false},
                        {field: 'pay_disc', title: __('Pay_disc'), operate: false},
                        {field: 'pay_all', title: __('Pay_all'), operate: false},
                        {field: 'wage_adjust', title: __('Wage_adjust'), operate: false},
                    ]
                ]
                ,onLoadSuccess: function(){
                    $('.search , .columns-right').hide();
                    $('.btn_export').unbind('click').click(function(){
                        $('.dropdown-menu li[data-type="excel"]').trigger('click');
                    });
                    $('#ctime').attr('autocomplete','off');
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
