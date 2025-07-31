define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mashangchannel/order/index' + location.search,
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
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'receiving_account_code', title: __('Receiving_account_code'), operate: false},
                        {field: 'receiving_account_id', title: __('Receiving_account_id'), operate:false},
                        {field: 'amount', title: __('Amount'), operate:false},
                        {field: 'real_pay_amount', title: __('Real_pay_amount'), operate:false},
                        {field: 'pay_channel_number', title: __('Pay_channel_number'), operate: '='},
                        {field: 'refund_number', title: __('Refund_number'), operate: '='},
                        {field: 'rate', title: __('Rate'), operate:false},
                        {field: 'rate_amount', title: __('Rate_amount'), operate:false},
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
                                    return '<p class="btn btn-default">订单超时</p>'
                                }
                            }
                        },
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
