```
var table = $("#table");

// 初始化表格
table.bootstrapTable({
url: '/Home/GetDepartment',         //请求后台的URL（*）用于从远程站点请求数据的URL
method: 'get',                      //请求方式（*）
toolbar: '#toolbar',                //工具栏按钮用哪个容器 一个jQuery 选择器，指明自定义的 buttons toolbar。例如:#buttons-toolbar, .buttons-toolbar 或 DOM 节点
toolbarAlign:'left'                 //指示如何对齐自定义工具栏。可以使用'left'，'right'
buttonsToolbar:'',                  //一个jQuery选择器，指示按钮工具栏，例如：＃buttons-toolbar，.buttons-toolbar或DOM节点
buttonsAlign:'right',               //指示如何对齐工具栏按钮。可以使用'left'，'right'。
buttonsClass:'secondary',           //定义表按钮的Bootstrap类（在'btn-'之后添加）
striped: true,                      //是否显示行间隔色
cache: false,                       //是否使用缓存，默认为true，所以一般情况下需要设置一下这个属性（*）
pagination: true,                   //是否显示分页（*） 设置为true以在表格底部显示分页工具栏默认false
sortable: true,                     //是否启用排序  列中也有此变量
sortName:'',                        //定义要排序的列   没定义默认都不排列，同sortOrder结合使用，sortOrder没写的话列默认递增（asc）
sortOrder: "asc",                   //定义列排序顺序，只能是'asc'或'desc'。
sortStable: false,                   //如果你把此属性设为了true）我们将为此行添加'_position'属性 （别看错了，是sortStable，sortable在下面）设为true，则和sort部分一样，区别是：在排序过程中，如果存在相等的元素，则原来的顺序不会改变
queryParams: oTableInit.queryParams,//传递参数（*）
sidePagination: "server",           //分页方式：client客户端分页（默认），server服务端分页（*）
silentSort:true,//设置为false以便对加载的消息数据进行排序。当sidePagination选项设置为“server”时，此选项有效。
pageNumber:1,                       //初始化加载第一页，默认第一页
pageSize: 10,                       //每页的记录行数（*）
pageList: [10, 25, 50, 100],        //可供选择的每页的行数（*）
search: true,                       //是否显示表格搜索input，此搜索是客户端搜索，不会进服务端，所以，个人感觉意义不大
strictSearch: true,                 //启用严格搜索
showColumns: false,                 //是否显示所有的列 设置为true以显示列下拉列表（一个可以设置显示想要的列的下拉f按钮）  
showRefresh: true,                  //是否显示刷新按钮 默认false
minimumCountColumns: 1,             //最少允许的列数  要从列下拉列表中隐藏的最小列数
clickToSelect: true,                //是否启用点击选中行
height: 500,                        //行高，如果没有设置height属性，表格自动根据记录条数觉得表格高度
idField:'',                         //表明哪个是字段是标识字段  
uniqueId: "ID",                     //表明每一行的唯一标识字段，一般为主键列
showToggle:true,                    //是否显示详细视图和列表视图的切换按钮
cardView: false,                    //是否显示详细视图  设置为true以显示卡片视图表，例如mobile视图（卡片视图）
detailView: false,                   //设置为true以显示detail 视图表（细节视图）
locale:'zh-CN',
height:800,                           //固定表格的高度
classes:'table table-bordered table-hover',//表的类名。可以使用'table'，'table-bordered'，'table-hover'，'table-striped'，'table-dark'，'table-sm'和'table-borderless'。默认情况下，表格是有界的。
theadClasses:'',// 表thead的类名 如使用.thead-light或.thead-dark使theads显示为浅灰色或深灰色。   
rowStyle:function(row,index){},//    行样式格式化程序函数支持类或css
rowAttributes:function(row,index){},//  row属性formatter函数，支持所有自定义属性  
undefinedText:'-',// 定义默认的未定义文本   
sortClass:'',//已排序的td元素的类名       
rememberOrder:false,//设置为true以记住每列的顺序    
data:[],//    要加载的数据 [] or {}
contentType:'application/json',//请求远程数据的contentType，例如：application/x-www-form-urlencoded。    
dataType:'json',//您希望服务器返回的数据类型    
totalField:'total',//Key in incoming json containing 'total' data.
dataField:'rows',//名称写自己定义的每列的字段名，也就是key，通过key才能给某行的某列赋value原文：获取每行数据json内的key
onlyInfoPagination:false,//设置为true以仅显示表中显示的数据量。它需要将分页表选项即pagination设置为true    
paginationLoop:true,//设置为true以启用分页连续循环模式    
paginationHAlign:'right',//分页条水平方向的位置，默认right（最右），可选left   
totalRows:0,//该属性主要由分页服务器传递，易于使用    
paginationDetailHAlign:'left',//如果解译的话太长，举个例子，paginationDetail就是“显示第 1 到第 8 条记录，总共 15 条记录 每页显示 8 条记录”，默认left（最左），可选right    
paginationVAlign:'bottom',//分页条垂直方向的位置，默认bottom（底部），可选top、both（顶部和底部均有分页条）    
paginationPreText:'<',//上一页的按钮符号    
paginationNextText:'>',//下一页的按钮符号    
paginationSuccessivelySize:5,//分页时会有<12345...80>这种格式而5则表示显示...左边的的页数   
paginationPagesBySide:1,//...右边的最大连续页数如改为2则 <1 2 3 4....79 80>   
paginationUseIntermediate:false,//计算并显示中间页面以便快速访问 true 会将...替换为计算的中间页数42    
searchOnEnterKey:false,// true时搜索方法将一直执行，直到按下Enter键（即按下回车键才进行搜索）   
trimOnSearch:true,//默认true，自动忽略空格        
searchAlign:'right',//指定搜索输入框的方向。可以使用'left'，'right'。    
searchTimeOut:500,//设置搜索触发超时    
searchText:'',//设置搜索文本框的默认搜索值  
showHeader:true,//设置为false以隐藏表头    
showFooter:false,//设置为true以显示摘要页脚行(固定也交 比如显示总数什么的最合适)    
showPaginationSwitch:false,//设置为true以显示分页组件的切换按钮    
showFullscreen:false,// 设置为true以显示全屏按钮   
smartDisplay:true,//设置为true以巧妙地显示分页或卡片视图    
escape:false,// 转义字符串以插入HTML，替换 &, <, >, “, `, 和 ‘字符  跳过插入HTML中的字符串，替换掉特殊字符
selectItemName:'btSelectItem',//  设置radio 或者 checkbox的字段名称   
clickToSelect:false,//设置为true时 在点击列时可以选择checkbox或radio
singleSelect:false,// 默认false，设为true则允许复选框仅选择一行(不能多选了？)
checkboxHeader:true,//设置为false以隐藏标题行中的check-all复选框 即隐藏全选框     
maintainSelected:false,// true时点击分页按钮或搜索按钮时，记住checkbox的选择项    设为true则保持被选的那一行的状态
icons:{//定义工具栏，分页和详细信息视图中使用的图标    
paginationSwitchDown: 'fa-caret-square-down',
paginationSwitchUp: 'fa-caret-square-up',
refresh: 'fa-sync',
toggleOff: 'fa-toggle-off',
toggleOn: 'fa-toggle-on',
columns: 'fa-th-list',
detailOpen: 'fa-plus',
detailClose: 'fa-minus',
fullscreen: 'fa-arrows-alt'
},
iconSize:'undefined',// 定义icon图表的尺寸大小html对应为data-icon-undefined （默认btn）、data-icon-lg 大按钮的尺寸（btn-lg）...;  这里的值依次为undefined => btnxs => btn-xssm => btn-smlg => btn-lg   
iconsPrefix:'fa',//定义图标集名称（FontAwesome的'glyphicon'或'fa'）。默认情况下，'fa'用于Bootstrap v4

    queryParamsType:'limit',//设置'limit'以使用RESTFul类型发送查询参数。    
    ajaxOptions:{},//提交ajax请求的其他选项。值列表：jQuery.ajax。    
    customSort:function(sortName,sortOrder,data){},//自定义排序功能（用来代替自带的排序功能），需要两个参数（可以参考前面）：    
    ajax:function(){},// 一种替换ajax调用的方法。应该实现与jQuery ajax方法相同的API       
    queryParams: function(params) { // 请求远程数据时，您可以通过修改queryParams来发送其他参数
        return params 
    },   
    responseHandler:function(res) { //在加载远程数据之前，处理响应数据格式，参数对象包含 
        return res 
    },   
    customSearch:function(data,text){// 执行自定义搜索功能替换内置搜索功能，需要两个参数   
        return data.filter(function (row) {return row.field.indexOf(text) > -1})
    },
    footerStyle:function(column){//  页脚样式格式化程序函数，只需一个参数 m默认｛｝  
        return {
            css: { 'font-weight': 'normal' }, 
            classes: 'my-class'
        }
    },
    detailFormatter:function(index,row,element){//前提：detailView设为true，启用了显示detail view。- 用于格式化细节视图- 返回一个字符串，通过第三个参数element直接添加到细节视图的cell（某一格）中，其中，element为目标cell的jQuery element    
        return '';
    },    detailFilter:function(index,row){//当detailView设置为true时，每行启用扩展。返回true并且将启用该行以进行扩展，返回false并禁用该行的扩展。默认函数返回true以启用所有行的扩展。    
        return true
    },    ignoreClickToSelectOn:function(element){// 包含一个参数：element: 点击的元素。返回 true 是点击事件会被忽略，返回 false 将会自动选中。该选项只有在 clickToSelect 为 true 时才生效。  
        return $.inArray(element.tagName, ['A', 'BUTTON']
    },   
   
    columns: [
        {checkbox: false},
        {radio: false},
        {
            radio: false,//此列转成radio上面单独领出来是应为有字段显示就不需要它呀
            checkbox: false,//此列转成checkbox  单独拎出来同上
            field: 'operate', //设置data-field的值
            title: __('Operate'),//设置data-field的值
            table: table,
            events: Table.api.events.operate,
            formatter: Table.api.formatter.operate,//单元格格式函数 this上下文是当前列对象
            formatter: function (value, row, index,field){},
            titleTooltip:'列标题工具提示文本。此选项还支持标题HTML属性',
            class:'定义列的类名',
            rowspan:1,//指定单元格应占用的行数。
            colspan:1,//指定单元格应占用的列数。
            align:'center',//指定如何对齐列数据。可以使用'left'，'right'，'center'。
            halign:'center',//指定如何对齐表头。可以使用'left'，'right'，'center'。
            falign:'center',//指示如何对齐表格页脚。可以使用'left'，'right'，'center'。
            valign:'middle',//指出如何对齐单元格数据。可以使用'top','middle','bottom'
            '10%',//列的宽度。如果未定义，宽度将自动扩展以适合其内容。格式'100px','10%'，100,如果想表格保持列自适应并且尺寸太小，则可以忽略这项（通过类等使用min / max-width）
            sortable:false,//设置为true以允许列可以排序。
            order:'asc',//默认排序顺序，只能是'asc'或'desc'。
            visible:true,//设置为false以隐藏列项。
            cardVisible:true,//设置为false以隐藏card 视图状态中的列项
            switchable:true,//设置为false以禁用可切换的列项
            clickToSelect:true,//设置为true时 在点击列时可以选择checkbox或radio
            footerFormatter:function(data){},//当前列对象函数该函数应返回一个字符串，其中包含要在页脚单元格中显示的文本
            events::{},//使用格式化函数时的单元事件监听器 四个参数event,value,row,index； html可以这么用 <th .. data-events="operateEvent">
            sorter:function(a,b,rowA,rowB){},//用于进行本地排序的自定义字段排序函数(第一个字段值，第二个字段值，第一行，第二行)
            sortName:'',//提供可自定义的排序名称，而不是标题中的默认排序名称或列的字段名称
            cellStyle:function(value,row,index,field){},//单元格样式格式化函数 支持classs和css
            searchable:true,//设置为true以搜索此列的数据。
            searchFormatter:true,//设置为true以搜索使用格式化数据
            escape:false,//转义字符串以插入HTML，替换 &, <, >, “, `, and ‘ 字符。
            showSelectTitle:false,//设置为true以使用'radio'或'singleSelect''复选框'选项显示列的标题。
        }

    ]
});
var operateEvents = {
/* 'click .like' 是类名？*/
'click .like': function (e, value, row, index) {}
}

```

```
    table.on('load-success.bs.table', function (e, data) {
    });
```


```
http://t.zoukankan.com/lichihua-p-10429606.html
```
