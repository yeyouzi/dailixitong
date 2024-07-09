define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'log.user_balance_log/index' + location.search,
                    multi_url: 'log.user_balance_log/multi',
                    table: 'user_balance_log',
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
                        {
                            field: 'platform_id',
                            title: __('Platform.name'),
                            visible: false,
                            searchList: function (column) {
                                return Template('platformListTpl', {});
                            }
                        },
                        {field: 'id', title: __('Id') , operate: false},
                        {field: 'platform.name', title: __('Platform.name') , formatter: Table.api.formatter.label , operate: false},
                        {field: 'user_id', title: __('User_id') , operate: false},
                        {field: 'user.nickname', title: __('User.nickname') , operate: false},
                        {field: 'user.mobile', title: __('User.mobile'), operate: false },
                        {field: 'money', title: __('Money'),  operate: false , formatter: Controller.api.formatter.money},
                        // {field: 'scene', title: __('Scene'), searchList: {"0":__('Scene 0'),"1":__('Scene 1')}, formatter: Table.api.formatter.normal , operate: false , visible: false},
                        {field: 'remark', title: __('Remark') , operate: false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            },
            formatter : {
                money: function (val){
                    return val > 0 ? `+${val}` : val;
                }
            }
        }
    };
    return Controller;
});