<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateShoppingListRelation extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('shopping_list_products');
        $table
        ->addForeignKey('shopping_list_id','shopping_lists','id',['delete' => 'CASCADE', 'update' => 'CASCADE'])
        ->addForeignKey('product_id','products','id',['delete' => 'CASCADE', 'update' => 'CASCADE'])
        ->update();

    }
}
