<?php
$hideFooter = $this->request->getParam('action') === 'register' ||
    $this->request->getParam('action') === 'login' ||
    $this->request->getParam('action') === 'resetPassword';
$footerClass = ($this->Identity->isLoggedIn() && !$isUiDemo)
    ? 'ml-0 lg:ml-[var(--layout-sidebar-width)]'
    : '';
?>
<footer class="py-4 mt-auto <?= $hideFooter ? 'hidden' : '' ?> <?= $footerClass ?>">
    <div class="w-full text-center text-xs text-brand">
        <ul class="list-none mb-3">
            <li>
                <a href="https://www.mission-ki.de"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="no-underline"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Visit MISSION KI Website">
                    MISSION KI - AI MADE IN GERMANY
                </a>
            </li>
            <li>
                <a href="https://www.acatech.de"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="no-underline"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="Visit acatech Website">
                    acatech – Deutsche Akademie der Technikwissenschaften
                </a>
            </li>
        </ul>
        <div class="mb-3 font-bold">
            <?= $this->Html->link(
                "Kontakt",
                "https://c213b305.sibforms.com/serve/MUIFAK3_n67iQp9eSpHVapApH9pIpI5EElJ3qgquCXh870DTL5k104Y5yjrxODQu4ulh-OgkTnY1ezKrwiLYO3DQxVy37OI1yT_qt1G-Jwu_1JvEup3vDbDtVUTws3eHqxlDA-cHWbTs3HqJyvXGMqbGA9fsxnWCTO-S-BS3dAku1tDVej3aN5YXFEXfYyVhPSdUWnfHFyWOeCQX",
                ['class' => 'no-underline mr-3',  'target' => '_blank', '_full' => true]
            ) ?>
            <?= $this->Html->link(
                __('Terms of Service'),
                "https://mission-ki.de/de/impressum",
                //['controller' => 'Pages', 'action' => 'display', 'imprint'],
                ['class' => 'no-underline mr-3',   'target' => '_blank']
            ) ?>
            <?= $this->Html->link(
                __('Privacy Policy'),
                "https://mission-ki.de/de/datenschutz",
                //['controller' => 'Pages', 'action' => 'display', 'privacy'],
                ['class' => 'no-underline', 'target' => '_blank']
            ) ?>
        </div>
        <div class="text-gray-500 text-xs">
            &copy; <?= date('Y') ?> All rights reserved. Version:
            <a href="https://github.com/Mission-KI/pruefportal/issues/new?template=bug_report.yaml" target="_blank" rel="noopener noreferrer" class="no-underline" id="packageVersion">0.0.0</a>
        </div>
    </div>
</footer>