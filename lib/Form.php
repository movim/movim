<?php

/************************************************************************************************
 * FORM CLASS                                                                                   ***
 *                                                                                              * *
 *    Usage:   This class is designed to ease the creation of forms in a php applcation. This   * *
 *             class was inspired by a similar class by MT Jordan <mtjo@netzero.net> available  * *
 *             from http://mtjo.f2o.org/forms/.                                                 * *
 *             You must include the class in your html file, this is easiest when you have the  * *
 *             class in your php include path, but may be done from anywhere.                   * *
 *                                                                                              * *
 *    Example: See included example.form.php                                                    * *
 *                                                                                              * *
 *    License: This program is free software; you can redistribute it and/or modify it under    * *
 *             the terms of the GNU General Public License as published by the Free Software    * *
 *             Foundation; either version 2 of the License, or (at your option) any later       * *
 *             version.                                                                         * *
 *             This program is distributed in the hope that it will be useful, but WITHOUT ANY  * *
 *             WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A  * *
 *             PARTICULAR PURPOSE.  See the GNU General Public License for more details.        * *
 *             You should have received a copy of the GNU General Public License along with     * *
 *             this program; if not, write to the Free Software Foundation, Inc., 51 Franklin   * *
 *             Street, Fifth Floor, Boston, MA  02110-1301 USA                                  * *
 *                                                                                              * *
 *    File:    class.form.php    Version: 1.0.0 Beta        Date:    12/17/2005                 * *
 *                                                                                              * *
 *    Author:  Shannon Brooks <sbrooks@dogdoo.net>                                              * *
 *                                                                                              * *
 *    WWW:     http://www.dogdoo.net                                                            * *
 *                                                                                              * *
 *    Shannon Brooks (c) 2005                                                                   * *
 *                                                                                              * *
 ************************************************************************************************ *
   ************************************************************************************************/

class Form {

/* Public Variables *******************************************************************************

   Usage: $form->variable = <value>; -- must be set inside of script, not here */

   var $newline;           // true for new lines after elements, default is true
   var $error;             // will contain any error information, useful for debugging
   var $outputxhtml;       // when true, will attempt to output valid xhtml, default is true
   var $singleline;        // when true, will output single line, default is false
   var $brafterlabel;      // when true, will output <br /> after </label> except for radio and
                           // checkbox inputs, default is false

/* Form Elements **********************************************************************************

   startForm() -- generates the opening form tag

   Usage: bool $form->startForm('action'[,'class or id','target','method']);

   Examples:
      $form->startForm(basename($_SERVER['PHP_SELF']),"#testForm") || die("error: ".$form->error);
      $form->startForm('somescript.php','someClass','_blank','get') || die("error: ".$form->error);
      
   Note: you shouldn't use target in xhtml */

   // start the <form> element
   function startForm($action=false,$class='',$target='',$method='post') {

      // verify action is set
      if(!$action) {
         $this->error = "action is required for startForm";
         return false;
         }
      else { $action = ' action="' . $action . '"'; }

      // select form action: post or get
      if (!preg_match("#^(get|post)$#",strtolower($method))) {
         $this->error = "method should be post or get";
         return false;
         }
      else { $method = ' method="' . $method . '"'; }

      // target value for form
      if(trim($target != '')) { $target = ' target="' . $target . '"'; }

      // determine class or ID
      $class = $this->ClassID($class);

      // generate form tag and attributes
      $this->output .= '<form' . $class . $action . $method . $target . ' enctype="multipart/form-data">' . ((!$this->singleline)?("\n"):('')) . (($this->outputxhtml)?('<div>' . ((!$this->singleline)?("\n"):(''))):(''));
      $this->formopen = true;

      return true;
      }

   // close the </form>
   function closeForm() {
      $this->output .=  (($this->outputxhtml)?('</div>' . ((!$this->singleline)?("\n"):(''))):('')) . '</form>' . ((!$this->singleline)?("\n"):(''));
      $this->formopen = false;
      return true;
      }

/* Hidded Type Inputs *****************************************************************************

   hiddenInput() -- generates a hidden input

   Usage: bool $form->hiddenInput('name','value'); */

   function hiddenInput($name=false,$value=false) {

   // verify name and value are set
   if(!$name || !$value) {
      $this->error = "name and value are required for hiddenInput";
      return false;
      }

   // generate tag
   $this->output .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />' . ((!$this->singleline)?("\n"):(''));

   return true;
   }

/* Text Type Inputs *******************************************************************************

   textInput() -- generates a text input

   Usage: bool $form->textInput('name'[,'label','class', 'label class or #id','tooltip','max size (numeric)','size (numeric)','readonly (true/false)','value','password (true/false)']); */

   function textInput($name=false,$label=false,$class=false,$lclass=false,$title=false,$maxsize=false,$size=false,$readonly=false,$value=false,$password=false) {

      // verify name is set
      if(!$name) {
         $this->error = "name is required for textInput";
         return false;
         }
      else { $nametag = ' name="' . $name . '" id="' . $name . '"'; }

      // determine if this is a password input
      if($password) { $type = ' type="password"'; }
      else { $type = ' type="text"'; }

      // determine class for input
      if(!$class) { $class = ''; }
      else { $class = ' class="' . $class . '"'; }

      // determine class or id for label class
      $lclass = $this->ClassID($lclass);

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      // max size of input, numeric and limits input length
      if(!$maxsize) { $maxsize = ''; }
      else { $maxsize = ' maxlength="' . $maxsize . '"'; }

      // size of input, numeric and not nessary with styles
      if(!$size) { $size = ''; }
      else { $size = ' size="' . $size . '"'; }

      // determine if input is readonly
      if(!$readonly) { $readonly = ''; }
      else { $readonly = ' readonly="readonly"'; }

      // determine if value is set
      if(!$value) { $value = ''; }
      else { $value = ' value="' . htmlspecialchars($value) . '"'; }

      // determine if label is set
      if(!$label) { $label = ''; }
      else { $label = '<label' . $lclass . ' for="' . $name . '">' . $label . '</label>' . (($this->brafterlabel)?('<br />'):('')) . ((!$this->singleline)?("\n"):('')); }

      // generate output
      $this->output .= $label . '<input' . $type . $nametag . $class . $title . $size . $maxsize . $readonly . $value . ' />' . (($this->newline)?("<br />"):('')) . ((!$this->singleline)?("\n"):(''));

      return true;
      }

/* Checkbox and Radio Type Inputs ***************************************************************************

   checkboxInput() -- generates a checkbox

   Usage: bool $form->checkboxInputs('type (checkbox/radio)','name','value'[,'checked (true/false)','labelpos (before/after)','class','label class or #id','tooltip']);

   Note:  concantonates the name and value for the id so that clicking on the label changes the checked state of radio buttons */

   function checkboxInput($type='checkbox',$name=false,$value=false,$label=false,$checked=false,$labelpos='after',$class=false,$lclass=false,$title=false) {

      // determine type is checkbox or radio
      if($type != 'checkbox' && $type != 'radio') {
         $this->error = "checkboxInput type must be checkbox or radio";
         return false;
         }
      else { $typetag = ' type="' . $type . '"'; }

      // verify name and value are set
      if(!$name || !$value) {
         $this->error = "name and value are required for checkboxInput";
         return false;
         }
      else { $nametag = ' name="' . $name . '" id="'. $name . (($type == 'radio')?($value):('')) . '" value="' . $value . '"'; }

      // determine if check box is checked
      if(!$checked) { $checked = ''; }
      else { $checked = ' checked="checked"'; }

      // determine class for input
      if(!$class) { $class = ''; }
      else { $class = ' class="' . $class . '"'; }

      // determine class or id for label class
      $lclass = $this->ClassID($lclass);

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      // determin if label is set
      if(!$label) { $label = ''; }
      else { $label = '<label' . $lclass . ' for="' . $name  . (($type == 'radio')?($value):('')) . '">' . $label . '</label>'; }

      // generate checkbox tag and attributes
      $checkbox = '<input' . $typetag . $nametag . $checked . $class . $title . ' />';

      // order label and checkbox according to labelpos
      $this->output .= (($labelpos == 'after')?($checkbox . ((!$this->singleline)?("\n"):('')) . $label):($label . ((!$this->singleline)?("\n"):('')) . $checkbox)) . (($this->newline)?("<br />"):('')) . ((!$this->singleline)?("\n"):(''));

      return true;
      }


/* File Upload Input ******************************************************************************

   fileInput() -- generates a file upload box

   Usage: bool $form->file('name'[,'label','class','label class or #id','tooltip','size (numeric)']); 

   Example file input: echo $form->file('file','Upload File: '); */

   function fileInput($name=false,$label=false,$class='',$lclass='',$title='',$size='') {

      // verify that name is set
      if(!$name) {
         $this->error = "fileInput requires that name be set";
         return false;
         }
      else { $nametag = ' name="' . $name . '" id="' . $name . '"'; }

      // determine class for input
      if(!$class) { $class = ''; }
      else { $class = ' class="' . $class . '"'; }

      // determine class or id for label class
      $lclass = $this->ClassID($lclass);

      // determine if size is set and generate tag
      if(!$size) { $size=''; }
      else { $size = ' size="' . $size . '"'; }

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      // determin if label is set
      if(!$label) { $label = ''; }
      else { $label = '<label' . $lclass . ' for="' . $name . '">' . $label . '</label>' . (($this->brafterlabel)?('<br />'):('')) . ((!$this->singleline)?("\n"):('')); }

      // generate file input tag and attributes
      $this->output .= $label . '<input type="file"' . $nametag . $class . $title . $size . ' />' . (($this->newline)?("<br />"):('')) . ((!$this->singleline)?("\n"):(''));

      return true;
      }

/* Textarea Input *********************************************************************************

   textareaInput() -- generates a textarea

   Usage: bool $form->textareaInput('name'[,'label','class','label class or #id','tooltip','default value','cols (numeric)','rows (numeric)','wrap type (soft/hard/off)','readonly (true/false)']);

   Examples:
      $form->textarea('comments','Please enter your comments:','thin_borders','bold',false,'Type comments here...',100,10);
      $form->textarea('license','License Agreement',false,false,'Please read carefully...','Some license info...',false,false,false,true); */

   function textareaInput($name=false,$label=false,$class=false,$lclass=false,$title=false,$value='',$cols=false,$rows=false,$wrap=false,$readonly=false) {

      // verify that name is set
      if(!$name) {
         $this->error = "textareaInput requires that name be set";
         return false;
         }
      else { $nametag = ' name="' . $name . '" id="' . $name . '"'; }

      // determine class for input
      if(!$class) { $class = ''; }
      else { $class = ' class="' . $class . '"'; }

      // determine class or id for label class
      $lclass = $this->ClassID($lclass);

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      // determine number of columns
      if(!$cols || !is_numeric($cols)) { $cols = (($this->outputxhtml)?(' cols=""'):('')); }
      else { $cols = ' cols="' . $cols . '"'; }

      // determine number of rows
      if(!$rows || !is_numeric($rows)) { $rows = (($this->outputxhtml)?(' rows=""'):('')); }
      else { $rows = ' rows="' . $rows . '"'; }

      // determine if textbox should be read only
      if(!$readonly) { $readonly = ''; }
      else { $readonly = ' readonly="readonly"'; }

      // determine wrap type
      if(!$wrap) { $wrap = ''; }
      else { $wrap = ' wrap="' . $wrap . '"'; }

      // determin if label is set
      if(!$label) { $label = ''; }
      else { $label = '<label' . $lclass . ' for="' . $name . '">' . $label . '</label>' . (($this->brafterlabel)?('<br />'):('')) . ((!$this->singleline)?("\n"):('')); }

      // generate textarea tag and attributes
      $this->output .= $label . '<textarea' . $nametag . $cols . $rows . $readonly . $wrap . $class . $title . '>' . $value . '</textarea>' . (($this->newline)?("<br />"):('')) . ((!$this->singleline)?("\n"):(''));

      return true;
      }

/* Select and Option Inputs ***********************************************************************

   startSelect(),addOption() and closeSelect() -- create a drop down list

   Usage:
      bool startSelect('name'[,'label','class','label class or #id','tooltip'])
      bool selectOption('value'[,'option text','selected (true/false)'])
      bool closeSelect()                                                                          */

   // start the <select> input
   function startSelect($name=false,$label=false,$class=false,$lclass=false,$title=false) {

      // verify that name is set
      if(!$name) {
         $this->error = "startSelect requires that name be set";
         return false;
         }
      else { $nametag = ' name="' . $name . '" id="' . $name . '"'; }

      // determine class for input
      if(!$class) { $class = ''; }
      else { $class = ' class="' . $class . '"'; }

      // determine class or id for label class
      $lclass = $this->ClassID($lclass);

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      // determin if label is set
      if(!$label) { $label = ''; }
      else { $label = '<label' . $lclass . ' for="' . $name . '">' . $label . '</label>' . (($this->brafterlabel)?('<br />'):('')); }

      // generate select tag
      $this->output .= $label . '<select' . $nametag . $class . $title . '>' . ((!$this->singleline)?("\n"):(''));

      // tell script that select tag is open
      $this->selectopen = true;

      return true;
      }

   // insert <option> tags for <select>
   function addOption($value=false,$option=false,$selected=false) {

      // verify that there is a select tag open
      if(!$this->selectopen) {
         $this->error = "there must be an open select tag";
         return false;
         }

      // verify that value is set
      if(!$value) {
         $this->error = "selectOption requires that value be set";
         return false;
         }

      // determine if option is selected
      if(!$selected) { $selected = ''; }
      else { $selected = ' selected="selected"'; }

      // determine if option text exisits
      if(!$option) { $option = $value; }

      // generate option tag and attributes
      $this->output .= '<option value="' . $value . '"' . $selected . '>' . $option . '</option>' . ((!$this->singleline)?("\n"):(''));

      return true;
      }

   // close the </select> input
   function closeSelect() {

      // verify that there is a select tag open
      if(!$this->selectopen) {
         $this->error = "there must be an open select tag";
         return false;
         }

      // generate ouput
      $this->output .= '</select>' . (($this->newline)?("<br />"):('')) . ((!$this->singleline)?("\n"):(''));

      // tell script that select tag is closed
      $this->selectopen = false;

      return true;
      }

/* Fieldset/Legend Tags ***************************************************************************

   startFieldset() and closeFieldset -- generate a fieldset

   Usage:
      bool $form->startFieldset(['legend','class or #id'])
      bool $form->closeFieldset()

   Note: Leave legend title empty for no heading */

   // start the <fieldset>
   function startFieldset($legend=false,$class=false) {

      // determine fieldset class or ID
      $class = $this->ClassID($class);

      // generate <legend></legend> tags
      if(!$legend) { $legend = ''; }
      else { $legend = '<legend>' . $legend . '</legend>' . ((!$this->singleline)?("\n"):('')); }
      
      // start fieldset tag
      $this->output .= '<fieldset' . $class . '>' . ((!$this->singleline)?("\n"):('')) . $legend;

      // tell script that fieldset is open
      $this->fieldsetopen = true;

      return true;
      }

   // close the </fieldset>
   function closeFieldset() {

      // verify that fieldset is open
      if(!$this->fieldsetopen) {
         $this->error = "there must be an open fieldset tag";
         return false;
         }

      // output </fieldset> tag
      $this->output .= '</fieldset>' . ((!$this->singleline)?("\n"):(''));

      // tell script that fieldset is closed
      $this->fieldsetopen = false;

      return true;
      }

/* Buttons ****************************************************************************************

   genericButton(), submitButton() and resetButton() -- used to generate buttons

   Usage:
      bool $form->genericButton(['javascript for onclick event','button text','class or #id','tooltip','image for image buttons','image border width (numeric)'])
      bool $form->submitButton(['javascript for onclick event','button text','default button (true/false)','class or #id','tooltip','image for image buttons','image border width (numeric)'])
      bool $form->resetButton(['javascript for onclick event','button text','default button (true/false)','class or #id','tooltip'])

   Examples:
      $form->submitButton('return checkForm(thisForm)','Send Form')
      $form->resetButton()

   Note: It would be better to assign the button an id and then dynamically assign javascript functions to the button.
   Note: If you choose to use image buttons, the border is not specified by default. If you don't set the style to have no border in css you must set the border width. */

   // standard general purpose button
   function genericButton($script=false,$value='Click Here',$class=false,$title=false,$img=false,$imgborder=false) {

      // determine whether button is image or standard button
      if(!$img) { $type = ' type="button"'; }
      else { $type = ' type="image" src="' . $img . ((is_numeric($imgborder))?('" border="' . $imgborder . '"'):('')); }

      // determine button class
      $class = $this->ClassID($class);

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      //determine if script is set
      if(!$script) { $script = ''; }
      else { $script = ' onclick="' . str_replace('"','\'',$script) . '"'; }

      // generate button
      $this->output .= '<form><input' . $type . ' value="' . $value . '"' . $script . $class . $title . ' /></form>' . (($this->newline)?("<br />"):('')) . ((!$this->singleline)?("\n"):(''));

      return true;
      }

   // submit button
   function submitButton($script=false,$value=false,$default=true,$class=false,$title=false,$img=false,$imgborder=false) {

      // determine whether button is image or standard button
      if(!$img) { $type = ' type="submit"'; }
      else { $type = ' type="image" src="' . $img . ((is_numeric($imgborder))?('" border="' . $imgborder . '"'):('')); }

      // determine button class
      $class = $this->ClassID($class);

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      // determine if button text is not default
      if(!$value) { $value = ''; }
      else { $value = ' value="' . $value . '"'; }

      //determine if script is set
      if(!$script) { $script = ''; }
      else { $script = ' onclick="' . str_replace('"','\'',$script) . '"'; }

      // generate submit button
      $this->output .= '<input' . $type . $value . $class . $title . $script . (($default && !$this->outputxhtml)?(' default="default"'):('')) . ' />' . ((!$this->singleline)?("\n"):(''));

      return true;
      }

   // reset button
   function resetButton($script=false,$value=false,$default=false,$class=false,$title=false) {

      // determine button class
      $class = $this->ClassID($class);

      // title for input (tooltip)
      if(!$title) { $title = ''; }
      else { $title = ' title="' . $title . '"'; }

      // determine if button text is not default
      if(!$value) { $value = ''; }
      else { $value = ' value="' . $value . '"'; }

      //determine if script is set
      if(!$script) { $script = ''; }
      else { $script = ' onclick="' . str_replace('"','\'',$script) . '"'; }

      // generate reset button
      $this->output .= '<input type="reset"' . $value . $class . $title . $script . (($default && !$this->outputxhtml)?(' default="default"'):('')) . ' />' . ((!$this->singleline)?("\n"):(''));

      return true;
      }

/* Insert Unaltered HTML **************************************************************************

   insertHTML() -- used to insert unaltered HTML code

   Usage: bool insertHTML('<p>Some <b>HTML</b> code...</p>'[,'true/false-add a new line after code']) */

   function insertHTML($code=false,$autonl=true) {
      if(!$code) {
         $this->error = "insertHTML needs a code argument";
         return false;
         }
      else {
         $this->output .= $code . (($this->newline && $autonl)?("<br />"):('')) . ((!$this->singleline)?("\n"):(''));
         return true;
         }
      }

/* Insert Linebreak *******************************************************************************

   insertBR() -- used to insert a <br /> when $this->newline = false

   Usage: bool insertBR() */

   function insertBR() {
      $this->output .="<br />" . ((!$this->singleline)?("\n"):(''));
      return true;
      }

/* Output Form ************************************************************************************

   getForm() -- get form output, returns false on error

   Usage: string getForm() */

   function getForm() {

      // make sure there are no preceeding errors
      if($this->error) {
         return false;
         }

      // check for open select tags
      elseif($this->selectopen) {
         $this->error = "You have an open select tag, please check your code.";
         return false;
         }

      // check for open fieldset
      elseif($this->fieldsetopen) {
         $this->error = "You have an open fieldset tag, please check your code.";
         return false;
         }

      // check for open form, if open just close it and return form
      elseif($this->formopen) {
         $this->closeForm();
         return $this->output;
         }

      // return form
      else {
         return $this->output;
         }
      }

/* Reset Form *************************************************************************************

   resetForm() -- resets form to original state

   Usage: $form->resetForm()

   Note: use this function when reusing the same form object on one page. */

   function resetForm() {
      $this->genForm();
      return true;
      }

/**************************************************************************************************
 * INTERNAL FUNCTIONS - Please do not use these functions or variables directly                   *
 **************************************************************************************************/

/* Private Variables ******************************************************************************/

   var $output;
   var $formopen;
   var $selectopen;
   var $fieldsetopen;

/* Internal function to get class or id from $_dyna_form_classID **********************************/

   function ClassID($ClassID) {

      // verify that there is a class set, check to make sure user didn't put a blank space
      // in a attempt to disable the class attribute or by accident
      if(trim($ClassID) == '' || !$ClassID) { return ''; }

      // determine if it is a class or id, ids should begin with #
      if(preg_match("#^#",$ClassID)) { return ' id="' . substr($ClassID,1) . '"'; }
      else { return ' class="' . $ClassID . '"'; }
      }

/* Internal function to initialize variables ******************************************************/

   function genForm() {
      $this->output        = '';
      $this->error         = false;
      $this->newline       = true;
      $this->formopen      = false;
      $this->selectopen    = false;
      $this->fieldsetopen  = false;
      $this->outputxhtml   = true;
      $this->singleline    = false;
      $this->brafterlabel  = false;
      }

   }
?>
