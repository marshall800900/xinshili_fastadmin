define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'merchant/payproduct/index' + location.search + '&merchant_id=' + Config.merchant_id,
                    add_url: 'merchant/payproduct/add?merchant_id=' + Config.merchant_id,
                    edit_url: 'merchant/payproduct/edit',
                    del_url: 'merchant/payproduct/del',
                    multi_url: 'merchant/payproduct/multi',
                    import_url: 'merchant/payproduct/import',
                    table: 'merchant_pay_product',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: 'merchant_id', title: __('Merchant_id'), operate: false},
                        {field: 'product_code', title: __('Product_code'), operate: false},
                        {field: 'pay_channel_info', title: __('Pay_channel_info'), operate: false},
                        {field: 'is_open', title: __('Is_open'), searchList: {"0":__('Is_open 0'),"1":__('Is_open 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'rate', title: __('Rate'), operate:false},
                        {field: 'min_amount', title: __('Min_amount'), operate:false},
                        {field: 'max_amount', title: __('Max_amount'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name: 'merchant_product',
                                    text: '编辑权重',
                                    classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                                    url: 'merchant/payproduct/changeWidth/id/{id}',
                                    extend: 'data-area=\'["88%", "88%"]\'',
                                    refresh:true,
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
                stickyHeader:true,
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
        changewidth: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                $(document).on("change", "#c-pay_channel_info", function(){
                    //插入
                    $(".sp_element_box .selected_tag").each(function (obj){
                        console.log(111);

                        let is_exist = 0;
                        let html = '', width_class = 'width-' + $(this).attr('itemvalue');

                        $('.width>div').each(function (){
                            if ($(this).attr('class').indexOf(width_class) != -1){
                                is_exist = 1;
                                return false;
                            }
                        });

                        let defaultValue = '', allWidth = '';
                        if ($('.width').attr('width-value') != undefined){
                            allWidth = $.parseJSON($('.width').attr('width-value'));
                            for (var pay_channel_id in allWidth){
                                if (pay_channel_id == $(this).attr('itemvalue')){
                                    defaultValue = allWidth[pay_channel_id];
                                }
                            }
                        }


                        if (is_exist != 1){
                            html = '<div class="form-group width-'+$(this).attr('itemvalue')+'">\n' +
                                '       <label class="control-label col-xs-12 col-sm-3">'+$(this).text()+'</label>\n' +
                                '       <div class="col-xs-12 col-sm-4"><input id="c-wdith-'+$(this).attr('itemvalue')+'" data-rule="required"  class="form-control pay_channel_width" name="row[width]['+$(this).attr('itemvalue')+']" type="number" value="'+defaultValue+'"></div>\n' +
                                '   </div>';
                        }

                        $('.width').append(html);
                    });


                    //剔除
                    $('.width>div').each(function (){
                        let is_exist = 0;
                        let width_class = $(this).attr('class');
                        // console.log(width_class);

                        let pay_channel_info = $('#c-pay_channel_info').val().split(',');

                        for (var i = 0; i <= pay_channel_info.length; i++) {
                            if (width_class == 'form-group width-' + pay_channel_info[i]){
                                is_exist = 1;
                            }
                        }

                        if (is_exist != 1){
                            width_class = width_class.split(' ');
                            $('.'+width_class[1]).remove();
                        }
                    });
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
