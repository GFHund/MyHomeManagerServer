<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Init extends AbstractMigration
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
        $userTable = $this->table('users');
        $userTable
        ->addColumn('user_name','string')
        ->addColumn('password','string')
        ->addColumn('is_admin','boolean',['default' => false])
        ->addColumn('jwt_key','string',['null' => true])
        ->create();
        $userTable->changeColumn('id','uuid',['null' => false])->save();

        $fidoKeysTable = $this->table('fido_keys');
        $fidoKeysTable
        ->addColumn('title','string')
        ->addColumn('fido_key','string')
        ->addColumn('user_id','uuid')
        ->create();
        $fidoKeysTable->changeColumn('id','uuid',['null' => false])->save();

        $groupsTable = $this->table('groups');
        $groupsTable
        ->addColumn('title','string')
        ->create();
        $groupsTable->changeColumn('id','uuid',['null' => false])->save();

        /*
        $permissionPropertysTable = $this->table('permission_propertys');
        $permissionPropertysTable
        ->addColumn('property_name','string')
        ->addColumn('permission_read','boolean',['default' => false])
        ->addColumn('permission_write','boolean',['default' => false])
        ->addColumn('permission_create','boolean',['default' => false])
        ->addColumn('permission_delete','boolean',['default' => false])
        ->create();
        $permissionPropertysTable->changeColumn('id','uuid',['null' => false])->save();
        */

        $productsTable = $this->table('products');
        $productsTable->addColumn('product_name','string')
        ->create();
        $productsTable->changeColumn('id','uuid',['null' => false])->save();

        $shoppingListsTable = $this->table('shopping_lists');
        $shoppingListsTable->addColumn('title','string')->create();
        $shoppingListsTable->changeColumn('id','uuid',['null' => false])->save();

        $shoppingListProductsTable = $this->table('shopping_list_products');
        $shoppingListProductsTable
        ->addColumn('amount','float')
        ->addColumn('unit','string')
        ->addColumn('shopping_list_id','uuid')
        ->addColumn('product_id','uuid')
        ->create();
        $shoppingListProductsTable->changeColumn('id','uuid',['null' => false])->save();

        $recipesTable = $this->table('recipes');
        $recipesTable
        ->addColumn('recipe_name','string')
        ->addColumn('person_number','smallinteger')
        ->addColumn('duration','smallinteger')
        ->create();
        $recipesTable->changeColumn('id','uuid',['null' => false])->save();

        $recipesTags = $this->table('recipes_tags',['id' => false]);
        $recipesTags
        ->addColumn('recipe_id','uuid')
        ->addColumn('tag_id','uuid')
        ->create();

        $recipeStepsTable = $this->table('recipe_steps');
        $recipeStepsTable
        ->addColumn('step_number','smallinteger')
        ->addColumn('step_description','text')
        ->addColumn('recipe_id','uuid')
        ->create();
        $recipeStepsTable->changeColumn('id','uuid',['null' => false])->save();

        $recipeIncredientsTable = $this->table('recipe_incredient');
        $recipeIncredientsTable
        ->addColumn('amount','float')
        ->addColumn('unit','string')
        ->addColumn('recipe_id','uuid')
        ->addColumn('product_id','uuid')
        ->create();
        $recipeIncredientsTable->changeColumn('id','uuid',['null' => false])->save();

        $contractsTable = $this->table('contracts');
        $contractsTable
        ->addColumn('title','string')
        ->addColumn('price_per_month','float')
        ->addColumn('until','timestamp',['null' => true])
        ->addColumn('cancellation','timestamp',['null' => true])
        ->create();
        $contractsTable->changeColumn('id','uuid',['null' => false])->save();

        $magazinesTable = $this->table('magazines');
        $magazinesTable
        ->addColumn('title','string')
        ->addColumn('uri','string')
        ->addColumn('topics','text')
        ->create();
        $magazinesTable->changeColumn('id','uuid',['null' => false])->save();

        $magazinesTags = $this->table('magazines_tags',['id' => false]);
        $magazinesTags
        ->addColumn('magazine_id','uuid')
        ->addColumn('tag_id','uuid')
        ->create();

        $tagsTable = $this->table('tags');
        $tagsTable->addColumn('tag_name','string')->create();
        $tagsTable->changeColumn('id','uuid',['null' => false])->save();

        $toDoListsTable = $this->table('todo_lists');
        $toDoListsTable
        ->addColumn('title','string')
        ->addColumn('active','boolean',['default' => false])
        ->create();
        $toDoListsTable->changeColumn('id','uuid',['null' => false])->save();

        $todoItemsTable = $this->table('todo_items');
        $todoItemsTable
        ->addColumn('todo_text','text')
        ->addColumn('active','boolean',['default' => false])
        ->addColumn('completed_until','timestamp',['null' => true])
        ->addColumn('warning_from','timestamp',['null' => true])
        ->addColumn('todo_list_id','uuid')
        ->create();
        $toDoListsTable->changeColumn('id','uuid',['null' => false])->save();

        $wikiPagesTable = $this->table('wiki_pages');
        $wikiPagesTable
        ->addColumn('title','string')
        ->addColumn('wiki_text','text')
        ->create();
        $wikiPagesTable->changeColumn('id','uuid',['null' => false])->save();

        $tagsWikiPagesTable = $this->table('tags_wiki_pages',['id' => false]);
        $tagsWikiPagesTable
        ->addColumn('tag_id','uuid')
        ->addColumn('wiki_page_id','uuid')
        ->create();
    }
}
