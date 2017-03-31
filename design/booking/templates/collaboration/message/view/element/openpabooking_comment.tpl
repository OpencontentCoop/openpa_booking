<li>
    <div class="icon"><i class="fa fa-clock-o"></i></div>
    <div class="title">
        {$item.created|l10n(shortdatetime)}
        <b>{$item_link.participant.participant.contentobject.name|wash()}</b>
    </div>
    <div class="content">
        <small>
            {$item.data_text1|wash}
        </small>
    </div>
</li>
