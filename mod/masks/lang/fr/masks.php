<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'masks', language 'fr'
 *
 * @copyright  2016 Edunao SAS (contact@edunao.com)
 * @author     Sadge (daniel@edunao.com)
 * @package    mod_masks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


// general strings - for use selecting a module type, or listing module types, etc
$string['modulename']                   = 'Mask';
$string['modulenameplural']             = 'Instances de Mask';
$string['modulename_help']              = 'Importez votre fichier PDF, masquez des zones et ajoutez des questions à destination des apprenants pour qu\'ils puissent révéler la totalité du document.';

// plugin administration strings
$string['pluginadministration']         = 'Administration du module Masks';
$string['pluginname']                   = 'Mask';

// plugin capacities
$string['masks:addinstance']            = 'Ajouter une activité Masks';
$string['masks:view']                   = 'Voir l\'activité Masks';

// admin settings
$string['settinghead_basics']           = 'Paramètres principaux';
$string['settinghead_configuration']    = 'Options avancées';
$string['settinghead_advanced']         = 'Options pour développeurs';
$string['settingname_cmdline_pdf2svg']  = 'Ligne de commande pour exécuter l\'utilitaire pdf2svg (celui-ci doit obligatoirement être installé sur le système pour que le plugin fonctionne)';
$string['settingname_debug']            = 'Afficher les informations de débogage';
$string['settingname_maskedit']         = 'Champs additionels dans l\'éditeur de questions';
$string['setting_fields_none']          = 'Aucun';
$string['setting_fields_h']             = 'Indice';
$string['setting_fields_hf']            = 'Indice et feedback';
$string['settingname_showghosts']       = 'Afficher les empreintes des masques passés';

// instance settings
$string['name']                         = 'Nom de l\'activité';

// Misc strings
$string['page-mod-masks-x']             = 'N\'importe quelle page du module MASKS';
$string['modulename_link']             = 'mod/masks/view';

// Messages displayed in notification area
$string['notReadyMsg']                  = 'L\'exercice n\'est pas prêt. Veuillez réessayer plus tard.';

// Texts for menus
$string['page']                         = 'Page';
$string['options']                      = 'Options';
$string['full-size']                    = 'Zoom 100%';
$string['reshow-masks']                 = 'Réafficher les masks';
$string['rehide-masks']                 = 'Recacher les masks';
$string['page-hidden']                  = 'Cacher la page';
$string['reupload']                     = 'Re-Importer le document';
$string['add-mask-menu']                = 'AJOUTER';
$string['mask-actions-group']           = ''; // 'Mask: ';
$string['edit-question']                = 'Editer';
$string['mask-style-menu']              = 'Style';
$string['mask-hidden']                  = 'Cacher';
$string['mask-deleted']                 = 'Supprimer';
$string['layout-save-group']            = ''; // 'Unsaved Changes: ';
$string['save-layout']                  = 'ENREGISTRER';
$string['left-group']                   = '';
$string['right-group']                  = '';
$string['gradeNamePass']                = 'Réponses<br>Parfaites';
$string['gradeNameToGo']                = 'Questions<br>Restantes';
$string['header_congratulations_text']  = 'Parfait !';
$string['masks-shift-right']            = 'Déplacer vers la droite';
$string['masks-shift-left']             = 'Déplacer vers la gauche';
$string['masks-retrieve-masks']         = 'Récupérer les masks';

// Texts for congratulation frame
$string['frame_congratulation']         = 'Félicitations';
$string['frame_congratulation_text']    = 'Vous avez répondu à toutes les questions';

// Text for mask-type-related frames
$string['label_title']                  = 'Titre';
$string['label_note']                   = 'Texte de la note';
$string['label_question']               = 'Texte de la question';
$string['label_answer']                 = 'Réponse correcte (une alternative par ligne)';
$string['label_valid_answers']          = 'Réponse correcte';
$string['label_response']               = 'Réponse';
$string['label_goodanswer']             = 'La réponse correcte';
$string['label_badanswer0']             = 'Réponses incorrectes';
$string['label_badanswer1']             = 'Autre réponse incorrect';
$string['label_badanswer2']             = 'Autre réponse incorrect';
$string['label_badanswer3']             = 'Autre réponse incorrect';
$string['label_badanswers']             = 'Réponses incorrectes';
$string['label_goodanswerhint']         = 'Retour en cas de bonne réponse';
$string['label_badanswerhint']          = 'Retour en cas de mauvaise réponse';
$string['label_userhint']               = 'Indice';
$string['label_showhint']               = 'Voir l\'indice';
$string['label_hidehint']               = 'Cacher l\'indice';
$string['label_showhelp']               = 'Voir l\'aide';
$string['label_hidehelp']               = 'Cacher l\aide';
$string['label_submit']                 = 'Valider';
$string['label_cancel']                 = 'Annuler';
$string['label_close']                  = 'Fermer';
$string['label_style']                  = 'Style';
$string['passanswer_title']             = 'Correct';
$string['passanswer_text']              = 'C\'est la bonne réponse';
$string['goodanswer_title']             = 'Parfait';
$string['goodanswer_text']              = 'Bravo. C\'est la bonne réponse.';
$string['finalanswer_title']            = 'Félicitations';
$string['finalanswer_text']             = 'Vous avez répondu correctement à toutes les questions.<br>'
                                        . 'Toutefois, plusieurs essaies ont été nécessaires';
$string['perfectanswer_title']          = 'Score parfait !';
$string['perfectanswer_text']           = 'Bravo !<br>'
                                        . 'Vous avez répondu correctement à toutes les questions du premier coup';
$string['wronganswer_title']            = 'Incorrect';
$string['wronganswer_text']             = 'Premier essai incorrect : La note pour cette question sera zéro';
$string['badanswer_title']              = 'Incorrect';
$string['badanswer_text']               = 'Merci de réessayer';

// Text for layour auto-save frames
$string['save-confirm-title']           = 'Enregistrer les modifications ?';
$string['save-confirm-text']            = 'Vous avez des modifications non enregistrées. Si vous choisissez de ne pas les enregistrer maintenant, elles seront perdues.';
$string['label_save']                   = 'Enregistrer';
$string['label_nosave']                 = 'Ne pas enregistrer';

// Text for upload frames
$string['upload-input-title']           = 'Importer un fichier PDF';
$string['upload-input-text']            = ''
    . 'Choisissez un document PDF à importer<br><br>'
    . 'REMARQUE : La taille maximale des fichiers pour votre serveur est définie par l\'administrateur du système. En cas de problème pour importer des fichiers lourds, merci de contacter votre administrateur.<br>'
    ;
$string['upload-wait-title']            = 'Importation du document';
$string['upload-wait-text']             = ''
    . 'L\'importation de votre fichier sur le serveur est en cours<br>'
    . 'Cette opération peut prendre un moment.<br><br>'
    . 'Une fois téléchargé, le fichier sera traité par le serveur<br><br>'
    . 'Ce message peut disparaitre avant que le traitement ne soit terminé<br><br>'
    . 'Merci de ne pas rafraîchir la page ou naviguer vers une autre page<br>'
    ;

$string['label_upload']                 = 'Importer';
$string['label_upload_complete']        = 'Terminé';
$string['failed-upload-title']          = 'Erreur';
$string['failed-upload-text']           = 'L\'importation a échouée, merci de réessayer ou de contacter l\'administrateur de la plateforme';

// cmdline_pdf2svg isn't correct
$string['failedcmdline-title']          = 'Ligne de commande incorrecte';
$string['failedcmdline-text']           = ''
        . 'La ligne de commande permettant d\'exécuter l\'utilitaire pdf2svg est incorrecte.<br>'
        . 'Merci de contacter l\'administrateur de la plateforme';

// Alert texts
$string['alert_uploadnofile']           = 'Pour commencer, merci d\'importer un fichier PDF';
$string['alert_uploadsuccess']          = 'Félicitations, votre document a bien été importé.';
$string['alert_reuploadsuccess']        = 'Votre document a bien été importé.<br>Si des pages contenant des masks ont été insérées ou supprimées, les boutons Déplacer vers la droite et Déplacer vers la gauche dans le menu d\'options permettent de déplacer les Masks vers la page où ils devraient maintenant être. ';
$string['alert_uploadfailed']           = 'Echec de l\'importation - merci de réessayer ou de contacter l\'administrateur de la plateforme';
$string['alert_firstMaskAdded']         = 'Faites glisser le cache pour le déplacer ou le redimensioner';
$string['alert_questionSaved']          = 'Les modifications ont été enregistrées';
$string['alert_changesSaved']           = 'Les modifications ont été enregistrées';
$string['alert_saveStyleChange']        = 'Cliquez sur le bouton Enregistrer pour enregistrer les changements de style';
$string['alert_savePageHidden']         = 'Une page a été cachée : Cliquez sur Enregistrer pour enregistrer cette modification.';
$string['alert_saveMaskHidden']         = 'Un Mask a été caché : Cliquez sur Enregistrer pour enregistrer cette modification.';
$string['alert_saveDeletion']           = 'Un Mask a été supprimé : Cliquez sur Enregistrer pour le supprimer définitivement';
$string['alert_saveChanges']            = 'Certaines modifications ne sont pas enregistrées';
$string['alert_studentGradePass']       = 'Réponse correcte';
$string['alert_studentGradeDone']       = '';
$string['alert_studentGradeFail']       = 'Réponse Incorrecte';
$string['alert_gradeNamePass']          = 'Réponses correctes';
$string['alert_gradeNameToGo']          = 'Questions restantes';
$string['alert_shiftRight']             = 'Les masks de la pages courante et des pages suivantes ont été déplacés vers ';
$string['alert_shiftLeft']              = 'Les masks des pages suivantes ont été déplacés vers la gauche.';
$string['alert_falsePage']              = 'Une fausse page a été créée.';  

// Textes sent down to the javascript for dynamic use in browser
$string['navigateaway']                 = 'Vous avez des modifications non enregistrées\nPour les enregistrer, cliquez sur \"'.$string['label_save'].'\"';

// Text strings for different mask types
$string['add-mask-qcm']                 = 'Question à Choix Multiples';
$string['add-mask-qtxt']                = 'Question Simple'; // simple = texte
$string['add-mask-basic']               = 'Note Temporaire';
$string['add-mask-note']                = 'Note Permanente';

$string['title_new_qcm']                = 'Question à Choix Multiples';
$string['title_new_qtxt']               = 'Nouvelle Question Simple';
$string['title_new_basic']              = 'Nouvelle Note Temporaire';
$string['title_new_note']               = 'Nouvelle Note Permanente';

$string['title_edit_qcm']               = 'Question à Choix Multiples';
$string['title_edit_qtxt']              = 'Question Simple';
$string['title_edit_basic']             = 'Note Temporaire';
$string['title_edit_note']              = 'Note Permanente';

$string['edithelpfeedback']             = ''
    . $string['label_goodanswerhint'] . ' / ' . $string['label_badanswerhint']
    . '<br>'
    . 'Champ optionnel qui remplace le feedback par défaut après qu\'un apprenant ait répondu à une question.'
    ;
$string['edithelphint']                 = ''
    . $string['label_userhint']
    . '<br>'
    . 'Information optionnelle donnée aux apprenants ayant répondu au moins une fois de manière incorrect '
    . 'et n\'ayant pas encore trouvé la réponse correcte à la question.<br><br>'
    ;
$string['edithelp_qcm']                 = ''
        . 'Ce masks correspond à une question à choix multiples dont les différents réponses sont affichées dans un ordre aléatoire'
    ;
$string['edithelp_qtxt']                = ''
    . 'Ce Mask correspond à une courte question texte auquel l\'apprenant doit répondre par quelques mots.'
    . '<br><br>'
    . 'Il est possible d\'ajouter plusieurs réponses valides à la question en saisissant chacune sur une ligne différente dans le champ Réponse.'
    ;
$string['edithelp_basic']               = ''
    . 'Affiche une note qui disparait après avoir été consultée.'
    . '<br><br>'
    . 'Ce type de Mask peut, par exemple, être utilisé pour donner les consignes au début d\'un exercice. '
    ;
$string['edithelp_note']                = ''
    . 'Affiche une annotation qui peut être consulté par l\'apprenant quand il le souhaite et qui ne disparait jamais'
    ;

$string['settingname_disable_qcm']      = 'Désactiver les Questions à Choix Multiples';
$string['settingname_disable_qtxt']     = 'Désactiver les Questions Simples';
$string['settingname_disable_basic']    = 'Désactiver les Notes Temporaires';
$string['settingname_disable_note']     = 'Désactiver les Notes Permanentes Permanent Note';

