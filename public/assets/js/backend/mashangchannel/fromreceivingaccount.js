define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: async function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mashangchannel/fromreceivingaccount/index' + location.search,
                    table: 'receiving_account',
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
                        {
                            field: 'charge_account',
                            title: __('Charge_account') + '/' + __('Charge_account_name'),
                            operate: '=',
                            formatter: function (value, row, index) {
                                return row.charge_account + '&nbsp;/&nbsp;' + row.charge_account_name;
                            }
                        },
                        {field: 'admin_id', title: __('Admin_id'), operate: '='},
                        {field: 'yesterday_amount', title: __('Yesterday_amount'), operate: false},
                        {field: 'today_amount', title: __('Today_amount'), operate: false},
                        // {field: 'balance', title: __('Balance'), operate: false},
                        // {
                        //     field: 'charge_amount',
                        //     title: __('Charge_amount') + '/' + __('Charge_amount_ing') + '/' + __('Real_charge_amount'),
                        //     operate: false,
                        //     formatter: function (value, row, index) {
                        //         return row.charge_amount + '&nbsp;/&nbsp;' + row.charge_amount_ing + '&nbsp;/&nbsp;' + row.real_charge_amount;
                        //     }
                        // },
                        {field: 'remark', title: __('Remark'), operate: false},
                        {field: 'create_fail_msg', title: __('Create_fail_msg'), operate: false},
                        // {field: 'create_fail_msg', title: __('Create_fail_msg'), operate: false},
                        {
                            field: 'is_open',
                            title: __('Is_open'),
                            searchList: await $.getJSON('mashangchannel/receivingaccount/getSearchList?model_name=getIsOpenList'),
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        // {
                        //     field: 'expired_time',
                        //     title: __('Expired_time'),
                        //     operate: 'RANGE',
                        //     addclass: 'datetimerange',
                        //     autocomplete: false,
                        //     formatter: Table.api.formatter.datetime
                        // },
                        // {
                        //     field: 'update_time',
                        //     title: __('Update_time'),
                        //     operate: false,
                        //     addclass: 'datetimerange',
                        //     autocomplete: false,
                        //     formatter: Table.api.formatter.datetime
                        // },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: await $.getJSON('mashangchannel/receivingaccount/getSearchList?model_name=getStatusList'),
                            formatter: Table.api.formatter.status
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
        checkonline: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                require.config({
                    paths:{
                        "qrcode":["/assets/js/jquery.qrcode.min"]
                    }
                });
                require(['qrcode'], function (jquery,Qrcode) {
                    $('.qrcode').qrcode({
                        render:"canvas",
                        width:200,
                        height:200,
                        text:$('.qr_code_url').val()
                    });
                })

                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
