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

class Help extends WidgetBase
{
    function WidgetLoad() {
        $this->addcss('help.css');
    }
    
    function build()
    {
        ?>
            <div id="help">
                
            <h2>Qu'est ce que Movim ?</h2>
            
            <p>Visitez la page <a href="http://wiki.movim.eu/fr:whoami" target="_blank">Qu'est ce que Movim ?</a> 
            pour connaitre un peu plus le projet, ses buts et comprendre son fonctionnement.</p>
            
            <h2>À quoi correspondent les petit fanions sur l'interface ?</h2>
            <center>    
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect white"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect green"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect orange"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect red"></div>
            <div style="width: 60px; height: 50px; display: inline-block;" class="protect black"></div>
            </center>
                
            <p>Ces 5 petits fanions vous permetent, en un coup d'oeil, de connaitre la confidentialité 
            des données contenus en dessous de ceux-ci.</p>
            
            <p>
                <ul class="clean">
                    <li>Blanc, les données ne sont visibles que par vous même</li>
                    <li>Vert, les données ne sont visibles que par le contact choisit</li>
                    <li>Orange, les données sont visibles par toute votre liste de contact</li>
                    <li>Rouge, les données sont visibles par tout le monde, au sein du réseau XMPP</li>
                    <li>Noir, les données sont visibles par tout le monde sur internet</li>
                </ul>
            </p>
            
            <!--<h2>Traduction de H. Rackham (1914)</h2>

            <p>"But I must explain to you how all this mistaken idea of 
            denouncing pleasure and praising pain was born and I will 
            give you a complete account of the system, and expound the 
            actual teachings of the great explorer of the truth, the 
            master-builder of human happiness.</p>
            
            <h3>No one rejects, dislikes,</h3> 
            Or avoids pleasure itself, because it is pleasure, but 
            because those who do not know how to pursue pleasure 
            rationally encounter consequences that are extremely 
            painful. Nor again is there anyone who loves or pursues or 
            desires to obtain pain of itself, because it is pain, but 
            because occasionally circumstances occur in which toil and 
            pain can procure him some great pleasure. To take a trivial 
            example, which of us ever undertakes laborious physical 
            exercise, except to obtain some advantage from it? But who 
            has any right to find fault with a man who chooses to enjoy 
            a pleasure that has no annoying consequences, or one who 
            avoids a pain that produces no resultant pleasure?"</p>
            
            <a href="http://wiki.movim.eu">See the Official Wiki</a>
            
            <h2>Section 1.10.33 du "De Finibus Bonorum et Malorum" de Ciceron (45 av. J.-C.)</h2>

            <p>"At vero eos et accusamus et iusto odio dignissimos 
            ducimus qui blanditiis praesentium voluptatum deleniti 
            atque corrupti quos dolores et quas molestias excepturi 
            sint occaecati cupiditate non provident, similique sunt 
            in culpa qui officia deserunt mollitia animi, id est laborum 
            et dolorum fuga. Et harum quidem rerum facilis est et 
            expedita distinctio. Nam libero tempore, cum soluta nobis 
            est eligendi optio cumque nihil impedit quo minus id quod 
            maxime placeat facere possimus, omnis voluptas assumenda 
            est, omnis dolor repellendus. Temporibus autem quibusdam et 
            aut officiis debitis aut rerum necessitatibus saepe eveniet 
            ut et voluptates repudiandae sint et molestiae non 
            recusandae. Itaque earum rerum hic tenetur a sapiente 
            delectus, ut aut reiciendis voluptatibus maiores alias 
            consequatur aut perferendis doloribus asperiores repellat."
            </p>-->
            </div>
        <?php
    }
}
