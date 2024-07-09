define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'report.achievement/index' + location.search,
                    add_url: 'report.achievement/add',
                    import_url: 'report.achievement/data_import',
                    table: 'achievement',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                searchFormVisible: true,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id') , operate: false},
                        {
                            field: 'platform_id',
                            title: __('Platform.name'),
                            visible: false,
                            searchList: function (column) {
                                return Template('platformListTpl', {});
                            }
                        },
                        {field: 'platform.name', title: __('Platform.name') , formatter: Table.api.formatter.label, operate: false},
                        {field: 'user_id', title: __('User_id') , visible:false , operate: false},
                        {field: 'app_id', title: __('App_id')},
                        {field: 'user.nickname', title: __('User.nickname'), operate: false},
                        {field: 'user.mobile', title: __('User.mobile'), operate: false},
                        {field: 'total_profit', title: __('Total_profit'), operate:false},
                        {field: 'balance', title: __('Balance'), operate:false},
                        {
                            field: 'date', title: __('Date'),
                            operate:false,
                            searchList: function (column) {
                                return Template('dateTpl', {name : 'date' , date:''});
                            }

                        },
                        {field: 'remark', title: __('Remark') , operate: false},
                        // {field: 'admin_id', title: __('Admin_id') , operate: false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
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