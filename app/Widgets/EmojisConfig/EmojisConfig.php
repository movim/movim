<?php

namespace App\Widgets\EmojisConfig;

use App\Emoji;
use App\EmojisPack;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;
use Respect\Validation\Validator;

class EmojisConfig extends Base
{
    public function load()
    {
        $this->addcss('emojisconfig.css');
        $this->addjs('emojisconfig.js');
    }

    public function ajaxHttpGet()
    {
        $view = $this->tpl();
        $view->assign('packs', EmojisPack::all());
        $view->assign('favorites', $this->me->emojis->keyBy('id'));
        $this->rpc('MovimTpl.fill', '#emojisconfig_widget', $view->draw('_emojisconfig'));
    }

    public function ajaxAddEditFavoriteForm(int $emojiId)
    {
        $emoji = Emoji::where('id', $emojiId)->first();

        if ($emoji) {
            $view = $this->tpl();
            $view->assign('favorite', $this->me->emojis()->where('id', $emojiId)->first());
            $view->assign('emoji', $emoji);
            Dialog::fill($view->draw('_emojisconfig_add_edit'));
        }
    }

    public function ajaxAddEditFavorite($form)
    {
        $emoji = Emoji::where('id', $form->emojiid->value)->first();

        if ($emoji && Validator::regex('/^[a-z0-9\-]+$/')->isValid($form->alias->value)) {

            if ($this->me->emojis()->wherePivot('alias', $form->alias->value)
                ->where('id', '!=', $emoji->id)
                ->exists()
            ) {
                Toast::send($this->__('emojisconfig.alias_conflict'));
                return;
            }


            $this->me->emojis()->detach($emoji->id);
            $this->me->emojis()->attach($emoji->id, ['alias' => $form->alias->value]);
            $this->rpc('Dialog_ajaxClear');

            Toast::send($this->__('emojisconfig.new_added'));
            $this->rpc('EmojisConfig_ajaxHttpGet');
        } else {
            Toast::send($this->__('emojisconfig.alias_error'));
        }
    }

    public function ajaxRemoveFavorite(int $emojiId)
    {
        $this->me->emojis()->detach($emojiId);
        $this->rpc('Dialog_ajaxClear');
        $this->rpc('EmojisConfig_ajaxHttpGet');
    }
}
