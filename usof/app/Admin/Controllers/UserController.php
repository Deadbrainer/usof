<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('fullname', __('Fullname'));
        $grid->column('email', __('Email'));
        $grid->column('password', __('Password'));
        $grid->column('role', __('Role'));
        $grid->column('avatar', __('Avatar'));
        $grid->column('rating', __('Rating'));
        $grid->column('remember_token', __('Remember token'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('is_verified', __('Is verified'));

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('fullname', __('Fullname'));
        $show->field('email', __('Email'));
        $show->field('password', __('Password'));
        $show->field('role', __('Role'));
        $show->field('avatar', __('Avatar'));
        $show->field('rating', __('Rating'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('is_verified', __('Is verified'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('name', __('Name'));
        $form->text('fullname', __('Fullname'));
        $form->email('email', __('Email'));
        $form->password('password', __('Password'));
        $form->text('role', __('Role'))->default('user');
        $form->image('avatar', __('Avatar'));
        $form->number('rating', __('Rating'));
        $form->text('remember_token', __('Remember token'));
        $form->switch('is_verified', __('Is verified'));

        return $form;
    }
}
