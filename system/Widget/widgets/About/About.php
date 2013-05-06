<?php

/**
 * @package Widgets
 *
 * @file Vcard.php
 * This file is part of MOVIM.
 * 
 * @brief A widget which display some help 
 *
 * @author Timothée	Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 3 may 2012
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class About extends WidgetBase
{
    function WidgetLoad() {
    }
    
    function build()
    {
        ?>
        <div class="tabelem padded" title="<?php echo t('About'); ?>" id="about">
            <p>Movim is an XMPP-based communication platform. All the project, except the following software and resources, is under 
                <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License v3</a>.
            </p>
            <h2><?php echo t('Thanks'); ?></h2>
            <dl>
                <dt>Developers</dt><dd><a href="http://framabearn.tuxfamily.org/">Jaussoin Timothée aka edhelas</a></dd>
                <dt></dt><dd><a href="https://launchpad.net/~nodpounod">Ho Christine aka nodpounod</a></dd>
                <dt></dt><dd><a href="https://etenil.net/">Pasquet Guillaume aka Etenil</a></dd>
                <dt>Translators</dt><dd>Arabic - <a href="https://launchpad.net/~gharbeia">Ahmad Gharbeia أحمد غربية</a></dd>
                <dt></dt><dd>Brazilian - <a href="https://launchpad.net/~dnieper650">millemiglia</a></dd>
                <dt></dt><dd>Chinese - <a href="https://launchpad.net/~dudumomo">dudumomo</a></dd>
                <dt></dt><dd>Danish - <a href="https://launchpad.net/~ole-carlsen-web">Ole Carlsen</a></dd>
                <dt></dt><dd>Dutch - <a href="https://launchpad.net/~laurens-debackere">Laurens Debackere</a></dd>
                <dt></dt><dd>English UK - <a href="https://launchpad.net/~kevinbeynon">Kevin Beynon</a>, <a href="https://launchpad.net/~influence-pc">Vincent</a></dd>
                <dt></dt><dd>Esperanto - <a href="https://launchpad.net/~eliovir">Eliovir</a></dd>
                <dt></dt><dd>Finish - <a href="https://launchpad.net/~e1484180">Timo</a></dd>
                <dt></dt><dd>French - 
                    <a href="https://launchpad.net/~hyogapag">Hyogapag</a>,
                    <a href="https://launchpad.net/~e1484180">Hélion du Mas des Bourboux</a>,
                    <a href="https://launchpad.net/~jonathanmm">JonathanMM</a>,
                    <a href="https://launchpad.net/~grossard-o">Ludovic Grossard</a>,
                    <a href="https://launchpad.net/~schoewilliam">Schoewilliam</a>,
                    <a href="https://launchpad.net/~influence-pc">Vincent</a>,
                    <a href="https://launchpad.net/~edhelas">edhelas</a>,
                    <a href="https://launchpad.net/~nodpounod">pou</a>
                </dd>
                <dt></dt><dd>German - 
                    <a href="https://launchpad.net/~q-d">Daniel Winzen</a>,
                    <a href="https://launchpad.net/~jonas-ehrhard">Jonas Ehrhard</a>,
                    <a href="https://launchpad.net/~jonatan-zeidler-gmx">Jonatan Zeidler</a>,
                    <a href="https://launchpad.net/~kili4n">Kilian Holzinger</a>,
                </dd>
                <dt></dt><dd>Greek - 
                    <a href="https://launchpad.net/~d-rotarou">Rotaru Dorin</a>,
                    <a href="https://launchpad.net/~yang-hellug">aitolos</a>
                </dd>
                <dt></dt><dd>Hebrew - <a href="https://launchpad.net/~genghiskhan">GenghisKhan</a></dd>
                <dt></dt><dd>Hungarian - <a href="https://launchpad.net/~batisteo">Baptiste Darthenay</a></dd>
                <dt></dt><dd>Italian - 
                    <a href="https://launchpad.net/~andrea-caratti-phys">Andrea Caratti</a>,
                    <a href="https://launchpad.net/~giacomo-alzetta">Giacomo Alzetta</a>,
                    <a href="https://launchpad.net/~emailadhoc">kimj</a>
                </dd>
                <dt></dt><dd>Japanese - <a href="https://launchpad.net/~fdelbrayelle">Franky</a></dd>
                <dt></dt><dd>Occitan (post 1500) - 
                    <a href="https://launchpad.net/~cvalmary">Cédric VALMARY (Tot en òc)</a>,
                    <a href="https://launchpad.net/~caillonm">Maime</a>
                </dd>
                <dt></dt><dd>Portuguese - 
                    <a href="https://launchpad.net/~jonathanmm">JonathanMM</a>,
                    <a href="https://launchpad.net/~dnieper650">millemiglia</a>
                </dd>
                <dt></dt><dd>Russian - 
                    <a href="https://launchpad.net/~ak099">Aleksey Kabanov</a>,
                    <a href="https://launchpad.net/~vsharmanov">Vyacheslav Sharmanov</a>,
                    <a href="https://launchpad.net/~hailaga">hailaga</a>,
                    <a href="https://launchpad.net/~salnsg">Сергей Сальников</a>,
                    <a href="https://launchpad.net/~mr-filinkov">DeforaD</a>
                </dd>
                <dt></dt><dd>Spanish - 
                    <a href="https://launchpad.net/~alejandrosg">Alejandro Serrano González</a>,
                    <a href="https://launchpad.net/~edu5800">Eduardo Alberto Calvo</a>,
                    <a href="https://launchpad.net/~estebanmainieri">Esteban Mainieri</a>,
                    <a href="https://launchpad.net/~victormezio">Oizem Mushroom</a>,
                    <a href="https://launchpad.net/~kaiser-715-deactivatedaccount">Ricardo Sánchez Baamonde</a>,
                    <a href="https://launchpad.net/~invrom">Ubuntu</a>,
                    <a href="https://launchpad.net/~orochiforja">orochiforja</a>
                </dd>
                <dt></dt><dd>Turkish - 
                    <a href="https://launchpad.net/~basaran-caner">Caner Başaran</a>,
                    <a href="https://launchpad.net/~emreakca">emre akça</a>
                </dd>
            </dl>
            
            <h2><?php echo t('Software'); ?></h2>
            <dl>
               <dt>Database Library</dt><dd>Modl - Movim DB Layer <a href="https://launchpad.net/modl">launchpad.net/modl</a> under AGPLv3</dd>
               <dt>XMPP Library</dt><dd>Moxl - Movim XMPP Library <a href="https://launchpad.net/moxl">launchpad.net/moxl</a> under AGPLv3</dd>
            </dl>
            <br />
            <dl>
               <dt>Map Library</dt><dd>Leaflet <a href="http://leafletjs.com/">leafletjs.com</a> under BSD</dd>
               <dt>WYSIWYG editor</dt><dd>STDEditor <a href="http://code.google.com/p/steditor/">code.google.com</a> under BSD</dd>
               <dt>HTML Fixer</dt><dd> Giulio Pons <a href="http://www.barattalo.it">barattalo.it</a></dd>
            </dl>
            <h2><?php echo t('Ressources'); ?></h2>
            <dl>
               <dt>Icons</dt><dd>Famfamfam <a href="http://www.famfamfam.com/">www.famfamfam.com</a> under CC BY 3.0</dd>
               <dt></dt><dd>Icomoon <a href="http://keyamoon.com/">by Keyamoon</a> under CC BY 3.0</dd>
            </dl>
        </div>
        <?php
    }
}
