define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        lists: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'manager/lists' + location.search,
                    // edit_url: 'manager/edit?empNum={row.empNum}',
                    table: 'manager_lists',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                // sortName: 'id',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        // {checkbox: true},
                        {field: 'detail', title: __('详情'), table: table,
                            buttons: [
                                {
                                    name: 'base',
                                    text:__('员工信息'),
                                    title: __('员工信息'),
                                    //图标
                                    icon: 'fa fa-user-circle',
                                    //btn-dialog表示为弹窗
                                    classname: 'btn btn-xs btn-default btn-dialog',
                                    //弹窗位置，//自带参数ids
                                    url: 'manager/detail?empNum={row.empNum}',
                                    //弹窗大小
                                    extend: 'data-area=\'["100%","100%"]\'',

                                }
                            ], operate:false, formatter: Table.api.formatter.buttons
                        },                        {field: 'empNum', title: __('EmpNum'), operate: 'LIKE'},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'idCard', title: __('IdCard'), operate: 'LIKE'},
                        {field: 'contactPhone', title: __('ContactPhone'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: Config.statusList, formatter: Table.api.formatter.status},
                        {field: 'education', title: __('Education'), operate: false},
                        {field: 'ethnicity', title: __('Ethnicity'), operate: false},
                        {field: 'morningLocation', title: __('MorningLocation'), operate: false},
                        {field: 'hireDate', title: __('HireDate'), operate: false},
                        {field: 'jobLevel', title: __('JobLevel'), operate: false},
                        {field: 'marriageStatus', title: __('MarriageStatus'), searchList: Config.marryList, formatter: Table.api.formatter.normal,operate: false},
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
