<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddProcessIdToCriteria extends BaseMigration
{
    /**
     * Change Method.
     *
     * quality_dimension_id: DA
     * title: TR-Z12 (Wirkt sich das Verhalten oder Ergebnis des KI-Systems substanziell auf das Handeln von natürlichen Personen oder auf persönliche Rechte aus?)
     * type_id: Applikationsfragen|Grundfragen|Erweiterungsfragen
     * value: Ja|Nein
     * protection_target_category_id: allgemein
     * category_id: Erklärbarkeit & Interpretierbarkeit
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('criteria');

        $table->addColumn('process_id', 'integer', [
            'default' => null,
            'limit' => 10,
            'null' => false,
        ])
            ->addIndex(
                $this->index('process_id')
                ->setName('idx_criteria_process_id')
            );
        $table->addColumn('value', 'integer', [
            'default' => null,
            'limit' => 2,
            'null' => false,
        ]);
        $table->addColumn('criterion_type_id', 'integer', [
            'default' => null,
            'limit' => 2,
            'null' => false,
        ]);
        $table->addColumn('question_id', 'integer', [
            'default' => null,
            'limit' => 2,
            'null' => false,
        ]);
        $table->addColumn('protection_target_category_id', 'integer', [
            'default' => null,
            'limit' => 2,
            'null' => false,
        ]);

        $table->update();
    }
}
