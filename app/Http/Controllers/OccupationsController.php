<?php

namespace App\Http\Controllers;

use App\Contracts\OccupationParser;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OccupationsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $occparser;

    public function __construct(OccupationParser $parser)
    {
        $this->occparser = $parser;
    }

    public function index()
    {
        return $this->occparser->list();
    }

    public function compare(Request $request)
    {
        $this->occparser->setScope('skills');
        $occupation_1 = $this->occparser->get($request->get('occupation_1'));
        $occupation_2 = $this->occparser->get($request->get('occupation_2'));

        /** IMPLEMENT COMPARISON **/

        $match = (int)$this->matchOcc($occupation_1, $occupation_2);
        /** IMPLEMENT COMPARISON **/

        return [
            'occupation_1' => $occupation_1,
            'occupation_2' => $occupation_2,
            'match' => $match
        ];
    }

    private function matchOcc(Array $occupation_1, Array $occupation_2) {
        $totalMatch = 0;
        $maxMatch = 0;

        foreach ($occupation_1 as $skillA) {
            foreach ($occupation_2 as $skillB) {
                if ($skillA[1] === $skillB[1]) { // Check if skill names match
                    $matchPercentage = min($skillA[0], $skillB[0]); // Match percentage is the minimum importance value
                    $totalMatch += $matchPercentage;
                    $maxMatch += max($skillA[0], $skillB[0]); // Calculate the maximum possible match
                    break; // Move to the next skill in $occupation_1
                }
            }
        }

        if ($maxMatch === 0) {
            return 0; // Handle the case when both occupations have no matching skills
        }

        return ($totalMatch / $maxMatch) * 100;
    }
}
