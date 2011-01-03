<?php

db::createTable('data-type', array(
   'id' => 'autoincrement',
   'name' => 'string',
   'database_type' => 'string',
   'default_value_code' => 'text',
   'list_code' => 'text',
   'edit_code' => 'text',
   'save_code' => 'text',
), 'id');

function addDataType($fields)
{
   $result = db::add('data-type', $fields);

   if (!$result)
   {
      echo "<strong>Error creating data-type '{$fields['name']}' (" . db::getErrorMessage() . ").</strong><br />\n";
      $success = false;
   }
   else
   {
      echo "Created data-type '{$fields['name']}'.<br />\n";
   }
}

addDataType(array(
   'name' => 'String',
   'database_type' => 'string',
   'default_value_code' => <<<EODEOD
\$TYPE_VAR_NAME->FIELD_NAME = '';
EODEOD
   ,
   'list_code' => <<<EODEOD
<td class="string-preview"><?php echo htmlspecialchars(\$TYPE_VAR_NAME->FIELD_NAME); ?></td>
EODEOD
   ,
   'edit_code' => <<<EODEOD
<div class="field string-field"><label for="field-FIELD_NAME">FIELD_NAME_HUMAN_READABLE</label> <input type="text" name="FIELD_NAME" id="field-FIELD_NAME" value="<?php echo htmlspecialchars(\$TYPE_VAR_NAME->FIELD_NAME); ?>" /></div>
EODEOD
   ,
   'save_code' => <<<EODEOD
// Validate FIELD_NAME
if (isset(\$_POST['FIELD_NAME']))
{
   // No validation for normal strings
   \$fields['FIELD_NAME'] = \$_POST['FIELD_NAME'];
}
EODEOD
   ,
));

addDataType(array(
   'name' => 'Textbox',
   'database_type' => 'text',
   'default_value_code' => <<<EODEOD
\$TYPE_VAR_NAME->FIELD_NAME = '';
EODEOD
   ,
   'list_code' => <<<EODEOD
<td class="textbox-preview"><?php echo htmlspecialchars(substr(\$TYPE_VAR_NAME->FIELD_NAME, 0, 50)); ?></td>
EODEOD
   ,
   'edit_code' => <<<EODEOD
<div class="field textbox-field"><label for="field-FIELD_NAME">FIELD_NAME_HUMAN_READABLE</label> <textarea name="FIELD_NAME" id="field-FIELD_NAME" rows="10" cols="100"><?php echo htmlspecialchars(\$TYPE_VAR_NAME->FIELD_NAME); ?></textarea></div>
EODEOD
   ,
   'save_code' => <<<EODEOD
// Validate FIELD_NAME
if (isset(\$_POST['FIELD_NAME']))
{
   // No validation for normal textboxes
   \$fields['FIELD_NAME'] = \$_POST['FIELD_NAME'];
}
EODEOD
   ,
));

addDataType(array(
   'name' => 'Integer',
   'database_type' => 'integer',
   'default_value_code' => <<<EODEOD
\$TYPE_VAR_NAME->FIELD_NAME = 0;
EODEOD
   ,
   'list_code' => <<<EODEOD
<td class="integer-preview"><?php echo \$TYPE_VAR_NAME->FIELD_NAME; ?></td>
EODEOD
   ,
   'edit_code' => <<<EODEOD
<div class="field integer-field"><label for="field-FIELD_NAME">FIELD_NAME_HUMAN_READABLE</label> <input type="text" name="FIELD_NAME" id="field-FIELD_NAME" value="<?php echo \$TYPE_VAR_NAME->FIELD_NAME; ?>" /></div>
EODEOD
   ,
   'save_code' => <<<EODEOD
// Validate FIELD_NAME
if (isset(\$_POST['FIELD_NAME']))
{
   if (!is_numeric(\$_POST['FIELD_NAME']) || strpos(\$_POST['FIELD_NAME'], '.') != false)
   {
      alert::addError('FIELD_NAME_HUMAN_READABLE must be an integer.');
   }
   else
   {
      \$fields['FIELD_NAME'] = \$_POST['FIELD_NAME'];
   }
}
EODEOD
   ,
));

addDataType(array(
   'name' => 'Float',
   'database_type' => 'float',
   'default_value_code' => <<<EODEOD
\$TYPE_VAR_NAME->FIELD_NAME = 0.0;
EODEOD
   ,
   'list_code' => <<<EODEOD
<td class="float-preview"><?php echo \$TYPE_VAR_NAME->FIELD_NAME; ?></td>
EODEOD
   ,
   'edit_code' => <<<EODEOD
<div class="field float-field"><label for="field-FIELD_NAME">FIELD_NAME_HUMAN_READABLE</label> <input type="text" name="FIELD_NAME" id="field-FIELD_NAME" value="<?php echo \$TYPE_VAR_NAME->FIELD_NAME; ?>" /></div>
EODEOD
   ,
   'save_code' => <<<EODEOD
// Validate FIELD_NAME
if (isset(\$_POST['FIELD_NAME']))
{
   if (!is_numeric(\$_POST['FIELD_NAME']))
   {
      alert::addError('FIELD_NAME_HUMAN_READABLE must be a number.');
   }
   else if (
   {
      \$fields['FIELD_NAME'] = \$_POST['FIELD_NAME'];
   }
}
EODEOD
   ,
));

addDataType(array(
   'name' => 'Checkbox',
   'database_type' => 'boolean',
   'default_value_code' => <<<EODEOD
\$TYPE_VAR_NAME->FIELD_NAME = false;
EODEOD
   ,
   'list_code' => <<<EODEOD
<td class="checkbox-preview"><?php echo ((\$TYPE_VAR_NAME->FIELD_NAME) ? 'Yes' : 'No'); ?></td>
EODEOD
   ,
   'edit_code' => <<<EODEOD
<div class="field checkbox-field"><label for="field-FIELD_NAME">FIELD_NAME_HUMAN_READABLE</label> <input type="checkbox" name="FIELD_NAME" id="field-FIELD_NAME" <?php if (\$TYPE_VAR_NAME->FIELD_NAME) echo 'checked="checked" '; ?>/></div>
EODEOD
   ,
   'save_code' => <<<EODEOD
// Validate FIELD_NAME
if (isset(\$_POST['FIELD_NAME']))
{
   \$fields['FIELD_NAME'] = 1;
}
else
{
   \$fields['FIELD_NAME'] = 0;
}
EODEOD
   ,
));

addDataType(array(
   'name' => 'File Upload',
   'database_type' => 'string',
   'default_value_code' => <<<EODEOD
\$TYPE_VAR_NAME->FIELD_NAME = NULL;
EODEOD
   ,
   'list_code' => <<<EODEOD
<td class="file-upload-preview"><?php echo htmlspecialchars(\$TYPE_VAR_NAME->FIELD_NAME); ?></td>
EODEOD
   ,
   'edit_code' => <<<EODEOD
<div class="field file-upload-field">
  <label for="field-FIELD_NAME">FIELD_NAME_HUMAN_READABLE</label>
<?php
if (\$TYPE_VAR_NAME->FIELD_NAME)
{
   echo "Current file: \$TYPE_VAR_NAME->FIELD_NAME";
   echo ", Upload new file:";
}
?>
  <input type="file" name="FIELD_NAME" id="field-FIELD_NAME" />
</div>
EODEOD
   ,
   'save_code' => <<<EODEOD
// Validate FIELD_NAME
if (isset(\$_FILES['FIELD_NAME']))
{
   if (empty(\$_FILES['FIELD_NAME']['name']))
   {
      // Do nothing, they didn't upload a new file
   }
   else if (\$_FILES['FIELD_NAME']['error'] > 0)
   {
      alert::addError("Error uploading FIELD_NAME ({\$_FILES['FIELD_NAME']['error']}).");
   }
   else
   {
      \$path = '/files/' . \$_FILES['FIELD_NAME']['name'];

      if (file_exists(TADPOLE_PATH . \$path))
      {
         alert::addError("File \$path already exists.");
      }
      else
      {
         \$ok = move_uploaded_file(\$_FILES['FIELD_NAME']['tmp_name'], TADPOLE_PATH . \$path);
         if (\$ok)
         {
            \$fields['FIELD_NAME'] = \$path;
         }
         else
         {
            alert::addError('Error moving file to ' . TADPOLE_PATH . \$path . '.');
         }
      }
   }
}
EODEOD
   ,
));

addDataType(array(
   'name' => 'Image Upload',
   'database_type' => 'string',
   'default_value_code' => <<<EODEOD
\$TYPE_VAR_NAME->FIELD_NAME = NULL;
EODEOD
   ,
   'list_code' => <<<EODEOD
<td class="image-upload-preview"><img src="<?php echo SITE_URL . \$TYPE_VAR_NAME->FIELD_NAME; ?>" style="max-width: 100px; max-height: 100px;" /></td>
EODEOD
   ,
   'edit_code' => <<<EODEOD
<div class="field image-upload-field">
  <label for="field-FIELD_NAME">FIELD_NAME_HUMAN_READABLE</label>
<?php
if (\$TYPE_VAR_NAME->FIELD_NAME)
{
   echo 'Current image: <img src="' . SITE_URL . \$TYPE_VAR_NAME->FIELD_NAME . '" />';
   echo ", Upload new image:";
}
?>
  <input type="file" name="FIELD_NAME" id="field-FIELD_NAME" />
</div>
EODEOD
   ,
   'save_code' => <<<EODEOD
// Validate FIELD_NAME
if (isset(\$_FILES['FIELD_NAME']))
{
   if (empty(\$_FILES['FIELD_NAME']['name']))
   {
      // Do nothing, they didn't upload a new file
   }
   else if (\$_FILES['FIELD_NAME']['error'] > 0)
   {
      alert::addError("Error uploading FIELD_NAME ({\$_FILES['FIELD_NAME']['error']}).");
   }
   else if (!in_array(\$_FILES['FIELD_NAME']['type'], array(
      'image/gif',
      'image/jpeg', 'image/pjpeg',
      'image/png',
      'image/bmp', 'image/x-windows-bmp',
      'image/tiff', 'image/x-tiff'
      )))
   {
      alert::addError('FIELD_NAME must be an image file.');
   }
   else
   {
      \$imagesFolder = TADPOLE_PATH . '/files/images';
      if (file_exists(\$imagesFolder) && is_dir(\$imagesFolder))
      {
         \$path = '/files/images/' . \$_FILES['FIELD_NAME']['name'];
      }
      else
      {
         \$ok = mkdir(\$imagesFolder);
         if (\$ok)
         {
            \$path = '/files/images/' . \$_FILES['FIELD_NAME']['name'];
         }
         else
         {
            \$path = '/files/' . \$_FILES['FIELD_NAME']['name'];
         }
      }

      if (file_exists(TADPOLE_PATH . \$path))
      {
         alert::addError("File \$path already exists.");
      }
      else
      {
         \$ok = move_uploaded_file(\$_FILES['FIELD_NAME']['tmp_name'], TADPOLE_PATH . \$path);
         if (\$ok)
         {
            \$fields['FIELD_NAME'] = \$path;
         }
         else
         {
            alert::addError('Error moving image to ' . TADPOLE_PATH . \$path . '.');
         }
      }
   }
}
EODEOD
   ,
));

?>
