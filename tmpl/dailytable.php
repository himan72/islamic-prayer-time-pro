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
               <?php echo $city." - ".$country ; ?> <br /><br />
               <span dir="rtl" style ="text-align:center; font-size: <?php echo $fsize;?>px;">
               <?php
                  if($lang=="ar" || $lang=="arlt"){
                    echo $arabichdate[3]." ".$arabichdate[2]." ".$arabichdate[1]." ".$arabichdate[0];
                  }
                ?>
            </span>
            <span style ="text-align:center; font-size: <?php echo $fsize;?>px;">
      <?php
        if ($lang=="lt" || $lang=="arlt"){
          echo "<br/>". $dhj." ".$latinhdate[1]." ".$latinhdate[2]." ".$latinhdate[0];
        }
         echo "<br />".JHtml::_('date', $lday, 'l F d Y');
      ?>
    </span>
          </div>
          <div class= "prayerbloc">
               <table class="tablegreen">
               <?php if ($ikamaon){ ?>
               <thead>
                 <tr>
                   <th></th>
                   <th><?php if ($athancheck){echo $athancheck;}else{ echo  JText::_('MOD_ISLAMIC_PRAYER_TIME_ATHAN');}?></th>
                   <th>
                     <?php if ($ikamacheck){echo $ikamacheck;}else{ echo  JText::_('MOD_ISLAMIC_PRAYER_TIME_IKAMA');}?>
                   </th>
                 </tr>
               </thead>
               <?php } ?>

               <tbody>
                 <tr>
                   <td><?php if ($fajrchk){echo $fajrchk;}else{ echo  JText::_('MOD_ISLAMIC_PRAYER_TIME_FAJR');}?></td>
                   <td><?php echo $times[0]; ?></td>
                   <?php if ($ikamaon){ ?>
                   <td><?php echo ikamaTime($times[0], $fajrikama); ?></td>
                   <?php } ?>
                 </tr>
                 <tr>
                   <td><?php if ($chouroukchk){echo $chouroukchk;}else{ echo JText::_('MOD_ISLAMIC_PRAYER_TIME_SUNRISE');}?></td>
                   <?php if ($ikamaon){ ?>
                   <td colspan=2 style="text-align: center;"><?php echo $times[1] ?></td>
                   <?php } else { ?>
                   <td><?php echo $times[1] ?></td>
                   <?php } ?>
                 </tr>
                 <tr>
                   <td><?php if ($dhurchk){echo $dhurchk;}else{ echo JText::_(
                          'MOD_ISLAMIC_PRAYER_TIME_ZUHR');}?></td>
                   <td><?php echo $times[2]; ?></td>
                   <?php if ($ikamaon){ ?>
                   <td><?php echo ikamaTime($times[2], $duhrikama); ?></td>
                   <?php } ?>
                 </tr>
                 <tr>
                   <td><?php  if ($asrchk){echo $asrchk;}else{ echo JText::_('MOD_ISLAMIC_PRAYER_TIME_ASR'
                         );}?></td>
                   <td><?php echo $times[3]; ?></td>
                   <?php if ($ikamaon){ ?>
                   <td><?php echo ikamaTime($times[3], $asrikama); ?></td>
                   <?php } ?>
                 </tr>
                 <tr>
                   <td><?php  if ($maghribchk){echo $maghribchk;}else{ echo JText::_(
                          'MOD_ISLAMIC_PRAYER_TIME_MAGHRIB');}?></td>
                   <td><?php echo $times[5]; ?></td>
                    <?php if ($ikamaon){ ?>
                   <td><?php echo ikamaTime($times[5], $maghribikama); ?></td>
                   <?php } ?>
                 </tr>
                 <tr>
                   <td><?php if ($ishaachk){echo $ishaachk;}else{ echo JText::_(
                          'MOD_ISLAMIC_PRAYER_TIME_ISHA');}?></td>
                   <td><?php echo $times[6]; ?></td>
                   <?php if ($ikamaon){ ?>
                   <td><?php echo ikamaTime($times[6], $ishaikama); ?></td>
                   <?php } ?>
                 </tr></tbody>
               </table>
               <ul class="modiptultimer nopad">

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
                         $asrjuirclbl; ?>
                          </li>
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