<main style="background-color: rgb(var(--movim-background));">
    <aside>
        <?php $this->widget('CommunityDataPublic'); ?>
    </aside>
    <div>
        <?php $this->widget('PublicNavigation');?>
        <hr />
        <?php $this->widget('Blog');?>
        <ul class="list">
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">cloud_queue</i>
                </span>
                <div>
                    <p class="center normal"><a target="_blank" href="https://movim.eu">Powered by Movim</a></p>
                </div>
            </li>
        </ul>
    </div>
</main>
