    <header class="app-header">
        <div class="app-header-inner">
            <div>
                <a href="<?=url();?>">
                    <img src="<?= WWWPATH; ?>assets/images/logo1.png" alt="" class="imgIcons" style="width:64px; height:auto;">
                </a>
                <!-- <h1 class="app-title"> -->

                    <!-- Mina bigårdar -->
                     <!-- BeeHave -->
                <!-- </h1>
                <p class="card-subtitle">Hantera bigårdar, kupor och gemensamma kommentarer</p> -->
            </div>
            <?php if (is_logged_in()) { ?>
            <?php if(1==2) { ?>
            <div class="app-user">
                <div><?= htmlspecialchars(current_user_name()) ?></div>
                <div>

                    <a href="<?=url('apiaries');?>">Bigård</a> |
                    <!-- <a href="<?=url();?>">Bikupa</a> | -->
                    <a href="<?=url('all_logs');?>">Alla Loggar</a> |
                    <!-- <a href="<?=url('settings');?>">Inställningar</a> | -->
                    <a href="<?=url('logout');?>">Logga ut</a>
                </div>
            </div>
            <?php }?>
            <div class="app-menu">
                <?php
                    $menu = [
                        'apiaries' => [
                            'url' => 'apiaries',
                            'icon' => 'bigard',
                            'caption' => 'Till bigårdar'
                        ],
                        'all_logs' => [
                            'url' => 'all_logs',
                            'icon' => 'logg',
                            'caption' => 'Visa alla loggar'
                        ],
                        'profile' => [
                            'url' => 'profile',
                            'icon' => 'profil',
                            'caption' => 'Till användarprofil'
                        ],
                        'logout' => [
                            'url' => 'logout',
                            'icon' => 'logout',
                            'caption' => 'Logga ut'
                        ],                                                                        
                    ];
                    foreach ($menu as $key => $value) {
                        print "<a href=\"" . url($value['url']) . "\" title='{$value['caption']}'><img src=\"" . WWWPATH . "assets/icons/ikon_{$value['icon']}.png\" alt=\"{$value['caption']}\" class=\"imgIcons\"></a>\n";        
                    }
                ?>
                <!-- <a href="<?=url('apiaries');?>" title="Till bigårdar"><img src="<?= WWWPATH; ?>assets/icons/ikon_bigard.png" alt="Till bigårdar" class="imgIcons"></a>
                <a href="<?=url('all_logs');?>"><img src="<?= WWWPATH; ?>assets/icons/ikon_logg.png" alt="" class="imgIcons"></a>
                <a href="<?=url('profile');?>"><img src="<?= WWWPATH; ?>assets/icons/ikon_profil.png" alt="" class="imgIcons"></a>
                <a href="<?=url('logout');?>"><img src="<?= WWWPATH; ?>assets/icons/ikon_logout.png" alt="" class="imgIcons"></a> -->
            </div>
            <!-- <div class="app-user">
                <a href="<?=url('apiaries');?>"><img src="<?= WWWPATH; ?>assets/icons/ikon_bigard.png" alt="" class="imgIcons"></a>
                <a href="<?=url();?>"><img src="<?= WWWPATH; ?>assets/icons/ikon_bikupa.png" alt="" class="imgIcons"></a>
                <a href="<?=url('all_logs');?>"><img src="<?= WWWPATH; ?>assets/icons/ikon_logg.png" alt="" class="imgIcons"></a>
            </div> -->
            <?php } ?>
        </div>
    </header>