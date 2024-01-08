define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'emp_exam/index' + location.search,
                    // add_url: 'emp_exam/add',
                    // edit_url: 'emp_exam/edit',
                    // del_url: 'emp_exam/del',
                    // multi_url: 'emp_exam/multi',
                    // import_url: 'emp_exam/import',
                    table: 'emp_exam',
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
                // searchFormVisible:true,
                commonSearch: false,//快速搜索
                visible: false,//浏览模式(卡片切换)、显示隐藏列、导出、通用搜索全部隐藏
                showToggle: false,//浏览模式可以切换卡片视图和表格视图两种模式
                showColumns: false,//列，可隐藏不显示的字段
                search:false,//快速搜索，搜索框
                showExport: false,//导出

                columns: [
                    [
                        // {field: 'emp_id', title: __('Emp_id'), operate: false},
                        {field: 'emp_id_2', title: __('Emp_id_2'), operate: false},
                        {field: 'status', title: __('Status'),searchList: Config.status_list, formatter: Table.api.formatter.status, operate: false},
                        {field: 'username', title: __('Username'), operate: false},
                        {field: 'age', title: __('Age'), operate: false},
                        {field: 'id_card', title: __('Id_card'), operate: false},
                        {field: 'tel', title: __('Tel'), operate: false},
                        {field: 'sex', title: __('Sex'), operate: false},
                        {field: 'cert_number', title: __('Cert_number'), operate: false},
                        {field: 'cert_date', title: __('Cert_date'), operate: false},
                        {field: 'cert_validity', title: __('Cert_validity'), operate: false},
                        // {field: 'hb', title: __('Hb'), operate: false},
                        {field: 'remark', title: __('Remark'), operate: false},
                        {field: 're_exam', title: __('Re_exam'), operate: false},
                        {field: 'exam_org', title: __('Exam_org'), operate: false},
                        {field: 'create_time', title: __('录入时间'), addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime, operate: false},
                    ]
                ]
                ,onLoadSuccess: function(){
                    $('#ctime').attr('autocomplete','off');
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
