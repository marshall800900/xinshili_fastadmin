define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'merchant/payorder/index' + location.search,
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
                            field: 'product_code',
                            title: __('Product_code'),
                            searchList: $.getJSON('getselectpage/getPayProduct?is_json=2')
                        },
                        {
                            field: 'order_number',
                            title: __('Order_number') + '/' + __('Merchant_number'),
                            operate: false,
                            align: 'lfet',
                            formatter: function (value, row, index) {
                                return row.order_number + '&nbsp;/&nbsp;' + row.merchant_number;
                            }
                        },
                        {field: 'order_number', title: __('Order_number'), operate: '=', visible: false},
                        {field: 'merchant_number', title: __('Merchant_number'), operate: '=', visible: false},
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
                                    if (row.status == 2){
                                        return '<p class="btn btn-danger">等待通知</p>'
                                    }
                                    return '<p class="btn btn-default">等待通知</p>'
                                }
                                if (value == 1) {
                                    return '<p class="btn btn-success">通知成功</p>'
                                }
                            }
                        },
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
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
