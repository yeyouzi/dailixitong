define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'log.withdrawal/index' + location.search,
                    add_url: 'log.withdrawal/add',
                    reason_url: 'log.withdrawal/reason',
                    audit_url: 'log.withdrawal/audit',
                    table: 'withdrawal',
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
                        {field: 'user_id', title: __('User_id'), operate: false},
                        {field: 'user.mobile', title: __('User.mobile'), operate: false },
                        {field: 'name', title: __('Name') , operate: false},
                        {field: 'money', title: __('Money'), operate: false },
                        {field: 'fee', title: __('Fee'), operate: false },
                        {field: 'pay_amount', title: __('Pay_amount'), operate: false , formatter: Controller.api.formatter.payAmount},
                        {field: 'account', title: __('Account') , operate: false},
                        {field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal},
                        {field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Controller.api.formatter.state},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'audit_time', title: __('Audit_time'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'remark', title: __('Remark') ,operate: false},
                        //{field: 'admin_id', title: __('Admin_id')},
                        //{field: 'admin_remark', title: __('Admin_remark')},
                        //{field: 'pay_no', title: __('Pay_no')},
                        //{field: 'pay_state', title: __('Pay_state'), searchList: {"0":__('Pay_state 0'),"1":__('Pay_state 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        {
                            field: 'operate_list',
                            width: "120px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            class: 'operate_list',
                            buttons: [
                                {
                                    name: 'remark',
                                    text: __('See_reason'),
                                    title: __('See_reason'),
                                    classname: 'btn btn-xs btn-warning btn-dialog btn-sea-reason',
                                    url: $.fn.bootstrapTable.defaults.extend.reason_url,
                                    visible: function (row) {
                                        if(row.state == 2){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'audit',
                                    text: __('Audit_record'),
                                    title: __('Audit_record'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    url: $.fn.bootstrapTable.defaults.extend.audit_url,
                                    visible: function (row) {
                                        if(row.state == 0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        }
                    ]
                ]
            });

            //绑定查看原因事件
            $(document).on('click' , '.see_reason' , function(){
                // alert('你好')
                let btnObj = $(this).parent().siblings('.operate_list').children('.btn-sea-reason')
                btnObj.click();
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        audit: function () {
            Controller.api.bindevent();
        },
        reason: function () {
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter : {
                payAmount : function (val , row) {
                    return row.money - row.fee;
                },
                state : function(val){
                    var text = __(`State ${val}`)
                    if(val == 2){
                        var color = 'danger';
                        text = text + `(点击查看原因)`;
                        var otherClass = 'see_reason'
                    }else if(val == 1){
                        var color = 'success';
                    }else{
                    }
                    return `<a href="javascript:;" class="searchit ${otherClass}" data-toggle="tooltip" title=""><span class="text-${color}">${text}</span></a>`
                }
            }
        }
    };
    return Controller;
});