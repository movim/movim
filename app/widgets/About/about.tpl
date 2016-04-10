<div class="tabelem" title="{$c->__('page.about')}" id="about">
    <h2 class="padded_top_bottom">Movim {$version} - {$c->__('page.about')} </h2>

    <ul class="list thick divided">
        <li>
            <span class="primary icon bubble color green"><i class="zmdi zmdi-info"></i></span>
            <p>{$c->__('page.about')}</p>
            <p class="all">{$c->__('about.info')} <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License v3</a>.</p>
        </li>
        <li class="subheader">
            <p>{$c->__('about.thanks')}</p>
        </li>
        <li>
            <span class="primary icon bubble color red"><i class="zmdi zmdi-code"></i></span>
            <p>{$c->__('about.developers')}</p>
            <p class="all">
                <a href="http://edhelas.movim.eu/">Jaussoin Timothée aka edhelas</a><br/>
                <a href="https://launchpad.net/~nodpounod">Ho Christine aka nodpounod</a><br/>
                <a href="https://etenil.net/">Pasquet Guillaume aka Etenil</a>
            </p>
        </li>
        <li>
            <span class="primary icon bubble color purple"><i class="zmdi zmdi-flag"></i></span>
            <p>{$c->__('about.translators')}</p>
            <p>
                {$c->__('about.translators_text')} <a href="https://www.transifex.com/projects/p/movim/">www.transifex.com/projects/p/movim/</a>
            </p>
        </li>
        <li class="subheader">
            <p>{$c->__('about.software')}</p>
        </li>
        <li>
            <span class="primary icon bubble color orange"><i class="zmdi zmdi-archive"></i></span>
            <p>{$c->__('about.software')}</p>
            <p class="all">
               Modl - Movim DB Layer - <a href="https://github.com/edhelas/modl">GitHub Modl</a> under AGPLv3<br/>
               Moxl - Movim XMPP Library - <a href="https://github.com/edhelas/moxl">GitHub Moxl</a> under AGPLv3<br/>

               Map Library - Leaflet <a href="http://leafletjs.com/">leafletjs.com</a> under BSD<br/>
               Chart.js - Nick Downie <a href="http://www.chartjs.org/">chart.js</a> under MIT<br/>
               Favico.js - Miroslav Magda <a href="http://lab.ejci.net/favico.js/">lab.ejci.net/favico.js</a> under GPL and MIT<br/>
               Markdown - Michel Fortin <a href="http://michelf.ca/projects/php-markdown/">michelf.ca</a> ©Michel Fortin<br/>
               Template Engine - RainTPL - Federico Ulfo <a href="http://www.raintpl.com/">www.raintpl.com</a> under MIT<br/>
               Embed - Oscar Otero <a href="https://github.com/oscarotero/Embed">GitHub Embed</a> under MIT<br/>
               Emoji - <a href="https://github.com/tompedals">Tom Graham</a> <a href="https://github.com/heyupdate/Emoji">GitHub HeyUpdate Emoji</a> under MIT<br/><br />
               WebSocket and Daemon engine - ReactPHP -  <a href="http://socketo.me/">socketo.me</a> ©Chris Boden<br/>
            </p>
        </li>
        <li>
            <span class="primary icon bubble color brown"><i class="zmdi zmdi-mood"></i></span>
            <p>{$c->__('about.resources')}</p>
            <p class="all">
                Material Design Iconic Font <a href="http://zavoloklom.github.io/material-design-iconic-font/icons.html">by Google and Sergey Kupletsky</a> under SIL OFL 1.1<br/>
                Twemoji <a href="http://twitter.github.io/twemoji/">by Twitter</a> under MIT and CC-BY<br/>
            </p>
        </li>
        <li>
            <span class="primary icon bubble color indigo"><i class="zmdi zmdi-import-export"></i></span>
            <p>{$c->__('about.api')}</p>
            <p class="all">
                OpenStreetMap - Nominatim <a href="http://nominatim.openstreetmap.org/">nominatim.openstreetmap.org</a><br/>
                Last.fm API - <a href="http://www.last.fm/api">www.last.fm/api</a><br/>
                Youtube API - <a href="http://developers.google.com/youtube">developers.google.com/youtube</a><br/>
            </p>
        </li>
    </ul>
</div>
