<form action="<?php echo url_for('trc/index') ?>" method="POST">
  <h1>Taux de rendement d'un capital</h1>
  <table>
  
    <?php echo $form ?>
    <tr>
      <td colspan="2">
        <input type="submit" />
      </td>

    </tr>
  </table>

<?php if (!$viewResultat)
	{ 
	?>
	<h6 class="title">Exemple </h6>

	<em class="annotation">
		vous avez placé un capital de 40 000 € pendant 5 ans et aujourd'hui, il s'élève à 55 000 €. Quel a été votre taux de rendement ?
	</em>
	
	<table>
		<tbody><tr>
			<td align="right" >Montant du capital placé</td>
			<td align="left" class="texte1">40000</td>
		</tr>

		<tr>
			<td align="right" >Durée du placement (en nombre de périodes)</td>
			<td align="left" >5</td>
		</tr>
		<tr>
			<td align="right" >Périodicité </td>
			<td align="left" >Ans</td>

		</tr>
		<tr>
			<td align="right" >Versements </td>
			<td align="left" >Fin</td>
		</tr>
		<tr>
			<td align="right" >Capital acquis</td>

			<td align="left" >55 000</td>
		</tr>
	</tbody></table>
	
	<h6>Résultat </h6>
	<em >
		un capital de 40 000 € placé en fin de période pendant 5 ans s'élève à 55 000 € si le taux du placement annuel proportionnel est de <b>7.48 %</b>, soit un taux annuel équivalent de <b>7.48 %</b>.
	</em>
	

	<?php
	} else {
		

		  		$t=$sf_user->getFlash('results');
  		echo  html_entity_decode($t['soap']); 

	}
	?>


</form>