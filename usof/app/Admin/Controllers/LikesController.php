<?php

namespace App\Admin\Controllers;

use App\Models\Likes;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LikesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Likes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Likes());

        $grid->column('id', __('Id'));
        $grid->column('author', __('Author'));
        $grid->column('post_id', __('Post id'));
        $grid->column('comment_id', __('Comment id'));
        $grid->column('type', __('Type'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Likes::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('author', __('Author'));
        $show->field('post_id', __('Post id'));
        $show->field('comment_id', __('Comment id'));
        $show->field('type', __('Type'));
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
        $form = new Form(new Likes());

        $form->number('author', __('Author'));
        $form->number('post_id', __('Post id'));
        $form->number('comment_id', __('Comment id'));
        $form->text('type', __('Type'))->default('like');

        return $form;
    }
}
