<?php if (!empty($_GET['type']) && $_GET['type'] == 'avatar') { ?><?php $this->widget('AvatarPlaceholder');?><?php } else { ?><?php $this->widget('Picture');?><?php } ?>
