define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/payorder/index' + location.search,
                    table: 'pay_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {field: 'merchant_id', title: __('Merchant_id')},
                        {
                            field: 'admin_id', title: __('Admin_id') + '/' + __('Receiving_account_id'),
                            align: 'lfet',
                            formatter: function (value, row, index) {
                                return row.admin_id + '&nbsp;/&nbsp;' + row.receiving_account_id ;
                            }
                        },
                        {
                            field: 'pay_channel_id',
                            title: __('Pay_channel_id'),
                            searchList: $.getJSON('getselectpage/getPaychannel?is_json=2')
                        },
                        {
                            field: 'product_code',
                            title: __('Product_code'),
                            searchList: $.getJSON('getselectpage/getPayProduct?is_json=2')
                        },
                        {
                            field: 'order_number',
                            title: __('Order_number') + '/' + __('Merchant_number') + '/' + __('Pay_channel_number'),
                            operate: false,
                            align: 'lfet',
                            formatter: function (value, row, index) {
                                return row.order_number + '&nbsp;/&nbsp;' + row.merchant_number + '&nbsp;/&nbsp;' + row.pay_channel_number;
                            }
                        },
                        {field: 'order_number', title: __('Order_number'), operate: '=', visible: false},
                        {field: 'merchant_number', title: __('Merchant_number'), operate: '=', visible: false},
                        {field: 'pay_channel_number', title: __('Pay_channel_number'), operate: '=', visible: false},
                        {
                            field: 'amount',
                            title: __('Amount') + '/' + __('Real_amount') + '/' + __('Refund_amount'),
                            operate: false,
                            align: 'lfet',
                            formatter: function (value, row, index) {
                                return row.amount + '&nbsp;/&nbsp;' + row.real_amount + '&nbsp;/&nbsp;' + row.refund_amount;
                            }
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: $.getJSON('getselectpage/getPayOrderStatus?is_json=2'),
                            formatter: function (value, row, index) {
                                if (value == 0) {
                                    return '<p class="btn btn-warning">订单创建</p>'
                                }
                                if (value == 1) {
                                    return '<p class="btn btn-default">等待支付</p>'
                                }
                                if (value == 2) {
                                    return '<p class="btn btn-success">支付成功</p>'
                                }
                                if (value == 3) {
                                    return '<p class="btn btn-danger">退款中</p>'
                                }
                                if (value == 4) {
                                    return '<p class="btn btn-danger">退款成功</p>'
                                }
                                if (value == 5) {
                                    return '<p class="btn btn-danger">订单超时</p>'
                                }
                            }
                        },
                        {
                            field: 'notify_status',
                            title: __('Notify_status'),
                            searchList: {"0": __('Notify_status 0'), "1": __('Notify_status 1')},
                            formatter: function (value, row, index) {
                                if (value == 0) {
                                    if (row.status == 2) {
                                        return '<p class="btn btn-danger">等待通知</p>'
                                    }
                                    return '<p class="btn btn-default">等待通知</p>'
                                }
                                if (value == 1) {
                                    return '<p class="btn btn-success">通知成功</p>'
                                }
                            }
                        },
                        // {field: 'notify_numer', title: __('Notify_numer')},
                        // {
                        //     field: 'request_time',
                        //     title: __('Request_time'),
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //     autocomplete: false,
                        //     formatter: Table.api.formatter.datetime
                        // },
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime,
                            defaultValue:Config.defaultDateValue
                        },
                        {
                            field: 'create_success_time',
                            title: __('Create_success_time'),
                            operate: false,
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'success_time',
                            title: __('Success_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        // {
                        //     field: 'refund_time',
                        //     title: __('Refund_time'),
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //     autocomplete: false,
                        //     formatter: Table.api.formatter.datetime
                        // },
                        {
                            field: 'user_ip',
                            title: __('User_ip') + '/' + __('User_ip_area') + '/' + __('User_device'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return row.user_ip + '&nbsp;/&nbsp;' + row.user_ip_area + '&nbsp;/&nbsp;' + row.user_device;
                            }
                        },
                        {field: 'create_fail_msg', title: __('错误日志'), operate: false},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'query',
                                    text: '查单',
                                    classname: 'btn btn-info btn-xs btn-detail btn-ajax',
                                    url: 'order/payorder/query',
                                    refresh: true,
                                    hidden: function (row) {
                                        if (row.status == 1 || row.status == 5) {
                                            return false;
                                        }
                                        return true;
                                    }
                                },
                                {
                                    name: 'budan',
                                    text: '补单',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'order/payorder/budan',
                                    refresh: true,
                                    hidden: function (row) {
                                        if (row.status == 1 || row.status == 5) {
                                            return false;
                                        }
                                        return true;
                                    }
                                },
                                // {
                                //     name: 'refund',
                                //     text: '退款',
                                //     classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                //     url: 'merchant/admin/changeBalance',
                                //     refresh:true,
                                //     hidden:function(row){
                                //         if (row.status == 2){
                                //             return false;
                                //         }
                                //         return true;
                                //     }
                                // },
                                {
                                    name: 'test_notify',
                                    text: '对接测试',
                                    classname: 'btn btn-info btn-xs btn-detail btn-ajax',
                                    url: 'order/payorder/testNotify',
                                    refresh: true,
                                    hidden: function (row) {
                                        if (row.status == 0) {
                                            return false;
                                        }
                                        return true;
                                    }
                                },
                                {
                                    name: 'notify',
                                    text: '重新通知',
                                    classname: 'btn btn-info btn-xs btn-detail btn-ajax',
                                    url: 'order/payorder/notify',
                                    refresh: true,
                                    hidden: function (row) {
                                        if (row.status == 2 && row.notify_status == 0) {
                                            return false;
                                        }
                                        return true;
                                    }
                                },
                            ]
                        }
                    ]
                ],
                showToggle: false,
                showColumns: true,
                showExport: false,
                search: false,
                commonSearch: true,
                searchFormVisible: true,
                pageList: [10, 20, 50],
                pageSize: 50,
                stickyHeader: true,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        budan: function () {
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
