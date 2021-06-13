<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Cake\Utility\Text;

class SettingsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $settingGroupTable = $this->table('setting_groups');
        $settingGroupTable
        ->addColumn('shown_label','string')
        ->create();
        $settingGroupTable->changeColumn('id','uuid',['null' => false])->save();

        $settingTable = $this->table('settings');
        $settingTable
        ->addColumn('technical_name','string')
        ->addColumn('shown_label','string')
        ->addColumn('group_id','string')
        ->addColumn('value_type','string')
        ->addColumn('value_id','string')
        ->create();
        $settingTable->changeColumn('id','uuid',['null' => false])->save();

        $settingFloatTable = $this->table('setting_floats');
        $settingFloatTable
        ->addColumn('setting_value','float')
        ->create();
        $settingFloatTable->changeColumn('id','uuid',['null' => false])->save();

        $settingIntTable = $this->table('setting_ints');
        $settingIntTable
        ->addColumn('setting_value','integer')
        ->create();
        $settingIntTable->changeColumn('id','uuid',['null' => false])->save();

        $settingBoolTable = $this->table('setting_booleans');
        $settingBoolTable
        ->addColumn('setting_value','boolean')
        ->create();
        $settingBoolTable->changeColumn('id','uuid',['null' => false])->save();

        $settingStringTable = $this->table('setting_strings');
        $settingStringTable
        ->addColumn('setting_value','string')
        ->create();
        $settingStringTable->changeColumn('id','uuid',['null' => false])->save();

        $settingGroupInsert = [
            'id' => Text::uuid(),
            'shown_label' => 'Magazine'
        ];
        $settingGroupTable->insert($settingGroupInsert);
        $settingGroupTable->saveData();

        $settingInsert = [
            [
                'id' => Text::uuid(),
                'technical_name' => 'magazine_indexer_ftp_address',
                'shown_label' => 'FTP Address',
                'group_id' => $settingGroupInsert['id'],
                'value_type' => 'strings',
                'value_id' => Text::uuid()
            ],
            [
                'id' => Text::uuid(),
                'technical_name' => 'magazine_indexer_ftp_username',
                'shown_label' => 'FTP Username',
                'group_id' => $settingGroupInsert['id'],
                'value_type' => 'strings',
                'value_id' => Text::uuid()
            ],
            [
                'id' => Text::uuid(),
                'technical_name' => 'magazine_indexer_ftp_password',
                'shown_label' => 'FTP Password',
                'group_id' => $settingGroupInsert['id'],
                'value_type' => 'strings',
                'value_id' => Text::uuid()
            ],
            [
                'id' => Text::uuid(),
                'technical_name' => 'magazine_indexer_ftp_directory',
                'shown_label' => 'Ftp Directory',
                'group_id' => $settingGroupInsert['id'],
                'value_type' => 'strings',
                'value_id' => Text::uuid()
            ]
        ];
        $settingTable->insert($settingInsert);
        $settingTable->saveData();

        $settingStringInsert = [
            [
                'id' => $settingInsert[0]['value_id'],
                'setting_value' => ''
            ],
            [
                'id' => $settingInsert[1]['value_id'],
                'setting_value' => ''
            ],
            [
                'id' => $settingInsert[2]['value_id'],
                'setting_value' => ''
            ],
            [
                'id' => $settingInsert[3]['value_id'],
                'setting_value' => ''
            ]
        ];
        $settingStringTable->insert($settingStringInsert);
        $settingStringTable->saveData();
    }
}
