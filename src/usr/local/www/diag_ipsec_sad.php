<?php
/* $Id$ */
/*
	diag_ipsec_sad.php
*/
/* ====================================================================
 *  Copyright (c)  2004-2015  Electric Sheep Fencing, LLC. All rights reserved. 
 *  Copyright (c)  2004-2009 Scott Ullrich
 *
 *  Redistribution and use in source and binary forms, with or without modification, 
 *  are permitted provided that the following conditions are met: 
 *
 *  1. Redistributions of source code must retain the above copyright notice,
 *      this list of conditions and the following disclaimer.
 *
 *  2. Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in
 *      the documentation and/or other materials provided with the
 *      distribution. 
 *
 *  3. All advertising materials mentioning features or use of this software 
 *      must display the following acknowledgment:
 *      "This product includes software developed by the pfSense Project
 *       for use in the pfSense software distribution. (http://www.pfsense.org/). 
 *
 *  4. The names "pfSense" and "pfSense Project" must not be used to
 *       endorse or promote products derived from this software without
 *       prior written permission. For written permission, please contact
 *       coreteam@pfsense.org.
 *
 *  5. Products derived from this software may not be called "pfSense"
 *      nor may "pfSense" appear in their names without prior written
 *      permission of the Electric Sheep Fencing, LLC.
 *
 *  6. Redistributions of any form whatsoever must retain the following
 *      acknowledgment:
 *
 *  "This product includes software developed by the pfSense Project
 *  for use in the pfSense software distribution (http://www.pfsense.org/).
  *
 *  THIS SOFTWARE IS PROVIDED BY THE pfSense PROJECT ``AS IS'' AND ANY
 *  EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 *  PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE pfSense PROJECT OR
 *  ITS CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *  NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 *  HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 *  STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 *  OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  ====================================================================
 *
 */

/*
	pfSense_BUILDER_BINARIES:	/sbin/setkey
	pfSense_MODULE: ipsec
*/

##|+PRIV
##|*IDENT=page-status-ipsec-sad
##|*NAME=Status: IPsec: SAD page
##|*DESCR=Allow access to the 'Status: IPsec: SAD' page.
##|*MATCH=diag_ipsec_sad.php*
##|-PRIV

require("guiconfig.inc");
require("ipsec.inc");

$pgtitle = array(gettext("Status"), gettext("IPsec"), gettext("SAD"));
$shortcut_section = "ipsec";
include("head.inc");

$sad = ipsec_dump_sad();

/* delete any SA? */
if ($_GET['act'] == "del") {
	$fd = @popen("/sbin/setkey -c > /dev/null 2>&1", "w");
	if ($fd) {
		fwrite($fd, "delete {$_GET['src']} {$_GET['dst']} {$_GET['proto']} {$_GET['spi']} ;\n");
		pclose($fd);
		sleep(1);
	}
}

$tab_array = array();
$tab_array[] = array(gettext("Overview"), false, "diag_ipsec.php");
$tab_array[] = array(gettext("Leases"), false, "diag_ipsec_leases.php");
$tab_array[] = array(gettext("SAD"), true, "diag_ipsec_sad.php");
$tab_array[] = array(gettext("SPD"), false, "diag_ipsec_spd.php");
$tab_array[] = array(gettext("Logs"), false, "diag_logs.php?logfile=ipsec");
display_top_tabs($tab_array);

if (count($sad)) {
?>
	<div table-responsive>
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?=gettext("Source")?></th>
					<th><?=gettext("Destination")?></th>
					<th><?=gettext("Protocol")?></th>
					<th><?=gettext("SPI")?></th>
					<th><?=gettext("Enc. alg.")?></th>
					<th><?=gettext("Auth. alg.")?></th>
					<th><?=gettext("Data")?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($sad as $sa) { ?>
			<tr>
				<td>
					<?=htmlspecialchars($sa['src'])?>
				</td>
				<td>
					<?=htmlspecialchars($sa['dst'])?>
				</td>
				<td>
					<?=htmlspecialchars(strtoupper($sa['proto']))?>
				</td>
				<td>
					<?=htmlspecialchars($sa['spi'])?>
				</td>
				<td>
					<?=htmlspecialchars($sa['ealgo'])?>
				</td>
				<td>
					<?=htmlspecialchars($sa['aalgo'])?>
				</td>
				<td>
					<?=htmlspecialchars($sa['data'])?></td>
				<td>
					<?php
						$args = "src=" . rawurlencode($sa['src']);
						$args .= "&amp;dst=" . rawurlencode($sa['dst']);
						$args .= "&amp;proto=" . rawurlencode($sa['proto']);
						$args .= "&amp;spi=" . rawurlencode("0x" . $sa['spi']);
					?>
					<a class="btn btn-xs btn-danger" href="diag_ipsec_sad.php?act=del&amp;<?=$args?>">Delete</a>
				</td>
			</tr>

			<?php
			} ?>
			</tbody>
		</table>
	</div>
<?php
		}
else
	print_info_box(gettext('No IPsec security associations.'));

print_info_box(gettext('You can configure your IPsec subsystem by clicking ') . '<a href="vpn_ipsec.php">' . gettext("here.") . '</a>');

include("foot.inc");