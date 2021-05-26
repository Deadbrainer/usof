<?php

namespace App\Admin\Controllers;

use App\Models\Posts;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class PostsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Posts';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Posts());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('content', __('Content'));
        $grid->column('author_id', __('Author id'));
        $grid->column('is_locked', __('Is locked'));
        $grid->column('is_locked_comments', __('Is locked comments'));
        $grid->column('categories', __('Categories'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            $actions->disableEdit();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Posts::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
        $show->field('author_id', __('Author id'));
        $show->field('is_locked', __('Is locked'));
        $show->field('is_locked_comments', __('Is locked comments'));
        $show->field('categories', __('Categories'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Posts());

        $form->text('title', __('Title'));
        $form->text('content', __('Content'));
        $form->number('author_id', __('Author id'));
        $form->number('is_locked', __('Is locked'));
        $form->number('is_locked_comments', __('Is locked comments'));
        $form->text('categories', __('Categories'));

        return $form;
    }
}
