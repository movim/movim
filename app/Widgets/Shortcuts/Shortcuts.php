<?php

namespace App\Widgets\Shortcuts;

use Moxl\Xec\Payload\Packet;

class Shortcuts extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addcss('shortcuts.css');
        $this->addjs('shortcuts.js');
        $this->registerEvent('notifs', 'onNotifs');
        $this->registerEvent('displayed', 'onDisplayed');
    }

    public function onDisplayed(Packet $packet)
    {
        $message = $packet->content;
        $this->rpc('Shortcuts.clear', $message->jidfrom);
    }

    public function onNotifs(Packet $packet)
    {
        $this->ajaxGet();
    }

    public function ajaxGet()
    {
        $notifs = linker($this->sessionId)->session->get('notifs', []);

        $jids = [];
        $notifs = array_reverse($notifs);

        foreach ($notifs as $key => $count) {
            $exploded = explode('|', $key);
            if (isset($exploded[1]) && $exploded[0] == 'chat') {
                $jids[$exploded[1]] = $count;
            }
        }

        $conferences = $this->me->session
            ->conferences()
            ->whereIn('conference', array_keys($jids))
            ->get()
            ->keyBy('conference');

        $contacts = $this->me->session
            ->contacts()
            ->whereIn('jid', array_keys($jids))
            ->get()
            ->keyBy('jid');

        $shortcuts = collect();

        foreach ($jids as $jid => $counter) {
            if ($conferences->has($jid)) {
                $element = $conferences->get($jid);
            } elseif ($contacts->has($jid)) {
                $element = $contacts->get($jid);
            } else {
                $element = \App\Contact::firstOrNew(['id' => $jid]);
            }

            if ($counter > 0) {
                $element->counter = $counter;
                $shortcuts->push($element);
            }
        }

        $this->rpc(
            'MovimTpl.fill',
            '#shortcuts_widget',
            $this->view('_shortcuts', ['shortcuts' => $shortcuts->slice(0, 3)])
        );
    }
}
