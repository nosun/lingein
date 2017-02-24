$(function() {
  $(['<div style="z-index:99999;width:170px;_width:auto;height:30px;line-height:30px;padding:0 10px;background-color:#ffc;border:1px solid #cc9;border-width:0 1px 1px 0;position:fixed">当前为预览模式<a href="', basePath + 'admin/site/unpreview/', '" style="margin-left:20px">[关闭预览]</a></div>'].join('')).prependTo('body');
});
