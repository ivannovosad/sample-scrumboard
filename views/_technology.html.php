<h2>TECHNOLOGY</h2>
<?php $totalPoints = $sprint->get_total_points(); ?>

<table>
  <tr>
      <th class="tech-ror">RoR</th>
      <td class="tech-ror">
        <?//= $sprint->get_ror_points(); ?> <?= round($sprint->get_ror_points() / $totalPoints * 100); ?>%
      </td>
  </tr>
  <tr>
      <th class="tech-drupal">Drupal</th>
      <td class="tech-drupal">
        <? //= $sprint->get_drupal_points(); ?> <?= round($sprint->get_drupal_points() / $totalPoints * 100); ?>%
      </td>
  </tr>
  <tr>
      <th class="tech-design">Design</th>
      <td class="tech-design">
        <?//= $sprint->get_design_points(); ?> <?= round($sprint->get_design_points() / $totalPoints * 100); ?>%
      </td>
  </tr>
  <tr>
      <th class="tech-infrastructure">Infrastructure</th>
      <td class="tech-infrastructure">
        <?//= $sprint->get_infrastructure_points(); ?>  <?= round($sprint->get_infrastructure_points() / $totalPoints * 100); ?>%
      </td>
  </tr>
  
</table>