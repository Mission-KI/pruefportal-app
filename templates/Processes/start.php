<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 */
?>
<div class="container mt-4" x-data="{ disclaimerAccepted: false }">
    <div class="row justify-content-center">
        <div class="col-lg-8">

              <?php ob_start(); ?>
                   <p>
                        Die Prüfung im Prüfportal findet gemäß des <?= $this->element('atoms/external_link', [
                                                                                                 'url' => 'https://mission-ki.de/de/pruefstandards',
                                                                                                 'text' => __('MISSION KI Qualitätsstandards'),
                                                                                                 'options' => ['class' => 'text-gray-100 underline end gap-2']
                                                                                             ]) ?> statt.
                        Sie ist ein strukturierter Prozess, der sicherstellt, dass ein KI-System nachvollziehbar, konsistent und methodisch geprüft wird.
                        <br/><br/>
                        Der Ablauf entspricht den Vorgaben aus <em>Kapitel 3 und 4</em> des Standards und ist im <em>MISSION KI Prüfportal</em> vollständig, mit kleineren Anpassungen abgebildet.
                        <br/><br/>


                          <?= $this->element('atoms/button', [
                              'url' => 'https://docs.pruefportal.mission-ki.de/entries/4',
                              'label' => __('Mehr zur Prüfung erfahren'),
                              'icon' => 'external-link',
                              'variant' => 'secondary',
                              'options' => [
                                'target' => '_blank'
                              ]
                          ]) ?>
                    </p>
              <?php $cardBody = ob_get_clean(); ?>



            <?= $this->element('molecules/primary_card', [
                'title' => h($process->project->title).' – '.h($process->title),
                'subtitle' => __('Prüfung im Prüfportal'),
                'body' => $cardBody,
                'escape' => false
            ]) ?>

            <div class="mt-4 mb-4">
                <?= $this->element('molecules/disclaimer', [
                    'type' => 'checkbox',
                    'textSize' => 'text-xl',
                    'attributes' => [
                        'x-model' => 'disclaimerAccepted'
                    ]
                ]) ?>
            </div>

            <div class="flex w-full justify-end">
                <div @click="if (!disclaimerAccepted) $event.preventDefault()"
                     :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': !disclaimerAccepted }">
                    <?= $this->element('atoms/button', [
                        'label' => __('Prüfprozess beginnen'),
                        'variant' => 'primary',
                        'url' => ['controller' => 'Processes', 'action' => 'startProcess', $process->id]
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
