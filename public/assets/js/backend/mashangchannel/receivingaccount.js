define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: async function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mashangchannel/receivingaccount/index' + location.search,
                    add_url: 'mashangchannel/receivingaccount/add?receiving_account_code=' + Config.receiving_account_code,
                    edit_url: 'mashangchannel/receivingaccount/edit',
                    del_url: 'mashangchannel/receivingaccount/del',
                    multi_url: 'mashangchannel/receivingaccount/multi',
                    import_url: 'mashangchannel/receivingaccount/import',
                    table: 'receiving_account',
                }
            });

            var table = $("#table");

            table.on('post-body.bs.table', function () {
                $(".btn-editone").data("area", ["60%", "80%"]);
            })

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
                        // {field: 'order_number', title: __('Order_number'), operate: '='},
                        {field: 'balance', title: __('Balance'), operate: false},
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
                            formatter: Table.api.formatter.toggle
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
                        // {
                        //     field: 'notify_status',
                        //     title: __('Notify_status'),
                        //     searchList: await $.getJSON('mashangchannel/receivingaccount/getSearchList?model_name=getNotifyStatusList'),
                        //     formatter: Table.api.formatter.status
                        // },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name: 'check_online',
                                    text: '在线检测',
                                    classname: 'btn btn-info btn-xs btn-detail btn-ajax',
                                    url: 'mashangchannel/Receivingaccount/checkOnline',
                                    hidden:function (row) {
                                      if (row.receiving_account_code == 'douyin' || row.receiving_account_code == 'dytb'){
                                          return false;
                                      }

                                      return true;
                                    },
                                    refresh: true
                                },
                                {
                                    name: 'quer_balance',
                                    text: '查询余额',
                                    extend: 'data-area=\'["88%", "88%"]\'',
                                    classname: 'btn btn-info btn-xs btn-detail btn-ajax',
                                    url: 'mashangchannel/Receivingaccount/queryBalance',
                                    hidden:function (row) {
                                      if (row.receiving_account_code == 'fxsh'){
                                          return false;
                                      }

                                      return true;
                                    },
                                    refresh: true
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
            $(document).on('click', '.btSelectAll', function () {
                $('.data-list-store tr').each(function () {
                    if ($(this).find('.ca-checkbox').find('input:checked').val() == 'on'){
                        $(this).find('.ca-checkbox').find('input').prop("checked", false);
                    }else{
                        $(this).find('.ca-checkbox').find('input').prop("checked", true);
                    }
                    return;
                })
            });

            $(document).on('click', '.btn-login', function () {
                let username = $("input[name='row[username]']").val();
                if (!username){
                    Backend.api.toastr.error('请输入账号');
                    return;
                }
                let password = $("input[name='row[password]']").val();
                if (!password){
                    Backend.api.toastr.error('请输入密码');
                    return;
                }
                let proxy_ip = $("input[name='row[proxy_ip]']").val();
                if (!proxy_ip){
                    Backend.api.toastr.error('请输入代理');
                    return;
                }

                $.ajax({
                    url: "mashangchannel/receivingaccount/login",
                    type: 'post',
                    dataType: 'json',
                    data: {
                        username:username,
                        password:password,
                        proxy_ip:proxy_ip,
                        receiving_account_code:Config.receiving_account_code,
                    },
                    success: function (ret) {
                        if (ret.code === 1) {
                            $('.data-list-store').html('');

                            let html = '';
                            $.each(ret.data, function (k, v) {
                                html += '<tr data-index="'+k+'" style="">\n' +
                                    '                                    <td className="bs-checkbox" class="ca-checkbox" style="text-align: center; ">\n' +
                                    '                                        <input data-index="'+k+'" name="row['+k+'][checkbox]" type="checkbox">\n' +
                                    '                                    </td>\n' +
                                    '<input type="hidden" name="row['+k+'][charge_account]" value="'+v.charge_account+'">\n' +
                                    '<input type="hidden" name="row['+k+'][charge_account_name]" value="'+v.charge_account_name+'">\n' +
                                    '<input type="hidden" name="row['+k+'][cookie]" value="'+v.cookie+'">\n' +
                                    '                                    <td style="text-align: left; vertical-align: middle; ">'+v.charge_account+'</td>\n' +
                                    '                                    <td style="text-align: left; vertical-align: middle; ">'+v.charge_account_name+'</td>\n' +
                                    '                                </tr>';
                            })
                            $('.data-list-store').html(html);
                        } else {
                            Backend.api.toastr.error(ret.msg);
                        }
                    }, error: function (e) {
                        Backend.api.toastr.error(e.message);
                    }
                });
            });
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
                });


                $('.send-sms-code').on('click', function () {
                    let proxy_ip = $('#c-proxy_ip').val();
                    let charge_account = $('#c-charge_account').val();
                    if (!proxy_ip){
                        alert('请输入代理IP');
                        return false;
                    }
                    if (!charge_account){
                        alert('请输入手机号');
                        return false;
                    }
                    $.ajax({
                        url: "mashangchannel/receivingaccount/sendSmsCode",
                        type: 'post',
                        dataType: 'json',
                        data: {
                            charge_account:charge_account,
                            proxy_ip:proxy_ip,
                            receiving_account_code:Config.receiving_account_code,
                        },
                        success: function (ret) {
                            if (ret.code === 1) {
                                $('#c-extra_params').val(ret.data);
                                Backend.api.toastr.success('发送成功');
                            } else {
                                Backend.api.toastr.error(ret.msg);
                            }
                        }, error: function (e) {
                            Backend.api.toastr.error(e.message);
                        }
                    });
                    return ;
                });

                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
