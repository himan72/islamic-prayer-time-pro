<?php
/**------------------------------------------------------------------------
# Islamic Prayer Pro module by MH Oudrhiri, joomlar.net
# ------------------------------------------------------------------------
# author    M Hicham Oudrhiri http://www.joomlar.net/
# Copyright @2014 Joomlar.net.  All Rights Reserved.
# @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
# Websites: http://www.joomlar.net/
-------------------------------------------------------------------------*/
// no direct access
     defined('_JEXEC') or die('Restricted access');
     $doc =JFactory::getDocument();
     $doc->addStyleSheet(JURI::root() . 'modules/mod_islamic_prayer_time_pro/assets/css/module-style.css');

?>
     <div class="islamicontainer" style="
                    <?php  if ($cwidth) { echo "width:".$cwidth."px; ";}?>
                    <?php  if ($cheight) { echo "height:".$cheight."px";}?>
     ">
          <div class="modiptcity">
               <?php echo $city." - ".$country ; ?> <br />
               <?php echo JHtml::_('date', $today, JText::_('DATE_FORMAT_LC1')) ?>
          </div>
          <div class= "prayerbloc">
               <ul class="modiptultimer">
                         <li class="modiptprayer"><?php if ($fajrchk){echo $fajrchk;}else{ echo  JText::_(
                         'MOD_ISLAMIC_PRAYER_TIME_FAJR');}?><?php echo ": ".$times[0]; 
                         if ($ikamaon){ echo ' '. JText::_('MOD_ISLAMIC_PRAYER_TIME_IKAMA_FIXED').': '. ikamaTime($times[0], $fajrikama);} ?>
                         </li>
                         <li class="modiptprayer"><?php if ($chouroukchk){echo $chouroukchk;}else{ echo JText::_(
                         'MOD_ISLAMIC_PRAYER_TIME_SUNRISE');}?><?php echo ": ".$times[1]; ?></li>

                          <li class="modiptprayer"><?php if ($dhurchk){echo $dhurchk;}else{ echo JText::_(
                          'MOD_ISLAMIC_PRAYER_TIME_ZUHR');}?><?php echo ": ".$times[2]; 
                           if ($ikamaon){ echo ' '. JText::_('MOD_ISLAMIC_PRAYER_TIME_IKAMA_FIXED').': '. ikamaTime($times[2], $duhrikama);}
                          ?></li>

                         <li class="modiptprayer"><?php  if ($asrchk){echo $asrchk;}else{ echo JText::_('MOD_ISLAMIC_PRAYER_TIME_ASR'
                         );}?><?php echo ": ".$times[3];
                         if ($ikamaon){ echo ' '. JText::_('MOD_ISLAMIC_PRAYER_TIME_IKAMA_FIXED').': '. ikamaTime($times[3], $asrikama);}
                          ?></li>

                          <li class="modiptprayer"><?php  if ($maghribchk){echo $maghribchk;}else{ echo JText::_(
                          'MOD_ISLAMIC_PRAYER_TIME_MAGHRIB');}?><?php echo ": ".$times[5];
                          if ($ikamaon){ echo ' '. JText::_('MOD_ISLAMIC_PRAYER_TIME_IKAMA_FIXED').': '. ikamaTime($times[5], $maghribikama);}

                          ?></li>

                          <li class="modiptprayer"><?php if ($ishaachk){echo $ishaachk;}else{ echo JText::_(
                          'MOD_ISLAMIC_PRAYER_TIME_ISHA');}?><?php echo ": ".$times[6]; 
                           if ($ikamaon){ echo ' '. JText::_('MOD_ISLAMIC_PRAYER_TIME_IKAMA_FIXED').': '. ikamaTime($times[6], $ishaikama);}
                          ?></li>

                              <?php
                         // Show Methode of Calculation  in Module
                         if ( $params->get ('showCalculMethod') == 1){?>
                         <li class="calcmethod"><?php echo JText::_('MOD_ISLAMIC_PRAYER_TIME_SHOW_CALCUL_METHOD_AUTHOR_LBL').": ".
                         $methodlbl;?></li>
                              <?php  }  ?>
                              <?php
                         // Show Asr juristic
                         if ( $params->get ('showAsrJuristic') == 1){?>
                         <li class="calcmethod"><?php echo JText::_('MOD_ISLAMIC_PRAYER_TIME_SHOW_ASR_JUIRIC_LBL').": ".
                         $asrjuirclbl; ?></li>
                              <?php  }  ?>
               </ul>
          </div>
          <div class="prayerblocfeatures">
               <?php
               // Show Author link in Module
               if ( $params->get ('showAuthorLink') == 1){
               echo '<span class="authorlink"><a href="http://Joomlar.net" target="_blank">Powered By Joomlar.net</a></span>';
               }?>
          </div>
     </div>
<?php //echo JText::_('MOD_ISLAMIC_PRAYER_TIME_SHOW_CALCUL_METHOD_AUTHOR_LBL').": ". JText::_('MOD_ISLAMIC_PRAYER_TIME_CALCUL_METHODE');?>