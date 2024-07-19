<?php

namespace App\Queries;

class OpponentsQuery extends CompetitorQuery
{
  protected $joinWith = 'opp_teams';

  protected function formWithCompetitor($form, $opponent)
  {
    return $form->map(function($match) use ($opponent) {
      return $match && $match->opponents->map->id->contains($opponent->id) ? $match : null;
    });
  }
}
