<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cur}}`.
 */
class m240818_090718_create_cur_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cur', [
            'id' => $this->primaryKey(),
            'char_code' => $this->string(10)->notNull(),
            'parser' => $this->integer(10)->notNull(),
            'name' => $this->string(50)->notNull(),
        ]);

        $this->execute(<<<SQL
INSERT INTO `cur`
VALUES (1, 'RUB', 1, 'Российский рубль'),
       (2, 'THB', 2, 'Таиландских батов');
SQL);

        $this->createTable('{{%cur_detail}}', [
            'id' => $this->primaryKey(),
            'datetime' => $this->dateTime(),
            'cur_id' => $this->integer(11)->notNull(),
            'value' => $this->string(20)->notNull(),
        ]);

        $this->addForeignKey('cur_cur_id_fk',
        'cur_detail',
        'cur_id',
            'cur',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('cur_cur_id_fk', 'cur_detail');
        $this->dropTable('{{%cur}}');
        $this->dropTable('{{%cur_detail}}');
    }
}
