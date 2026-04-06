<section>
    <ul class="list thick">
        <li>
            <span class="primary icon">
                <i class="material-symbols red">call_end</i>
            </span>
            <div>
                <p class="line">
                    <strong>{$c->__('visio.end_call_for_all')}</strong>
                </p>
                <p>{$c->__('visio.end_call_for_all_text')}</p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button onclick="Visio_ajaxEndMuji('{$mujiId|echapJS}'); Dialog_ajaxClear()" class="button color red">
      <i class="material-symbols">call_end</i>
    </button>
</footer>
