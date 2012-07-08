<ul>
<?php foreach ($forms as $form):?>
  <li><a href="<?php echo $this->Url('/form/' . $form->id);?>"><?php _h($form->title);?></a></li>
<?php endforeach;?>
</ul>
