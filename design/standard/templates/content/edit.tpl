{*{default content_object=$node.object
         content_version=$node.contentobject_version_object
         node_name=$node.name}*}

<form enctype="multipart/form-data" method="post" action={concat("/content/edit/",$object.id,"/",$edit_version,"/",$edit_language|not|choose(array($edit_language,"/"),''))|ezurl}>
<table class="layout" width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td valign="top">
    <!-- Left part start -->
    <div class="maincontentheader">
    <h1>{"Edit"|i18n("design/standard/content/edit")} {$class.name} - {$object.name}</h1>
    </div>

    {include uri="design:content/edit_validation.tpl"}

    {include uri="design:content/edit_placement.tpl"}
    <br/>

    {include uri="design:content/edit_attribute.tpl"}

    <div class="buttonblock">
    <input class="defaultbutton" type="submit" name="PublishButton" value="{'Send for publishing'|i18n('design/standard/content/edit')}" />
    <input class="button" type="submit" name="StoreButton" value="{'Store draft'|i18n('design/standard/content/edit')}" />
    <input class="button" type="submit" name="DiscardButton" value="{'Discard'|i18n('design/standard/content/edit')}" />
    </div>
    <!-- Left part end -->
    </td>
    <td width="120" align="right" valign="top" style="padding-left: 16px;">

    <!-- Right part start-->
    {include uri="design:content/edit_right_menu.tpl"}
    <!-- Right part end -->

    </td>
</tr>
</table>

</form>
