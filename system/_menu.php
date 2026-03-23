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
            <!-- <div class="app-user">
                <a href="<?=url('apiaries');?>"><img src="<?= WWWPATH; ?>assets/images/ikon_bigard.png" alt="" class="imgIcons"></a>
                <a href="<?=url();?>"><img src="<?= WWWPATH; ?>assets/images/ikon_bikupa.png" alt="" class="imgIcons"></a>
                <a href="<?=url('all_logs');?>"><img src="<?= WWWPATH; ?>assets/images/ikon_logg.png" alt="" class="imgIcons"></a>
            </div> -->
            <?php } ?>
        </div>
    </header>