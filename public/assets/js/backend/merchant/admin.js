define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'merchant/admin/index' + location.search,
                    add_url: 'merchant/admin/add',
                    edit_url: 'merchant/admin/edit',
                    del_url: 'merchant/admin/del',
                    multi_url: 'merchant/admin/multi',
                    import_url: 'merchant/admin/import',
                    table: 'admin',
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
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'loginfailure', title: __('Loginfailure'), operate: false},
                        {field: 'logintime', title: __('Logintime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'loginip', title: __('Loginip'), operate: false},
                        {field: 'createtime', title: __('Createtime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"lock":__('Lock')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name: 'merchant_product',
                                    text: '产品授权',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'merchant/payproduct/index/merchant_id/{id}',
                                    extend: 'data-area=\'["88%", "88%"]\'',
                                    refresh:true,
                                    hidden:function(row){
                                        if (!row.order_number){
                                            return false;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'merchant_balance',
                                    text: '积分变更',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'merchant/admin/changeBalance',
                                    refresh:true,
                                    hidden:function(row){
                                        if (!row.order_number){
                                            return false;
                                        }
                                        return true;
                                    }
                                }
                            ]}
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
                stickyHeader:true,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        copycreatepayurl:function () {
            $(document).on('click', '.btn-embossed', function () {
                Fast.api.ajax({
                    url: "merchant/admin/copyCreatePayUrl",
                    data: {
                        merchant_id: $("input[name='row[merchant_id]']").val(),
                        product_code: $("input[name='row[product_code]']").val()
                    },
                }, function (data, ret) {
                    if (ret.code != 1){
                        layer.msg(ret.msg);
                    }else{
                        var textarea = document.createElement('textarea');
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = 0;
                        textarea.value = data.url;
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);

                        layer.msg('已复制');
                    }
                    return false;
                });
            })
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        changebalance: function () {
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
