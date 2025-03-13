<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AudioController extends AbstractController
{
    #[Route('/audio/generate', name: 'audio_generate', methods: ['GET'])]
    public function generateAudio(): Response
    {
        $message = $_GET['message'] ?? null;
        $frequency = intval($_GET['frequency'] ?? 20000);

        // Validation
        if (empty($message)) {
            return new Response('Le paramètre "message" est requis.', Response::HTTP_BAD_REQUEST);
        }
        if ($frequency < 20 || $frequency > 22000) {
            return new Response('Fréquence invalide. Doit être entre 20 Hz et 22 kHz.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $pythonScriptPath = $this->getParameter('kernel.project_dir') . '/public/python/generate_audio.py';

            $command = sprintf(
                'python3 %s %s %d %d',
                escapeshellarg($pythonScriptPath),
                escapeshellarg($message),
                $frequency,
                44100
            );

            $process = proc_open(
                $command,
                [
                    1 => ['pipe', 'w'], // Sortie standard
                    2 => ['pipe', 'w'], // Sortie d'erreur
                ],
                $pipes
            );

            $audioData = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $returnCode = proc_close($process);

            if ($returnCode !== 0) {
                throw new \Exception($errors);
            }

            return new Response(
                $audioData,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'audio/wav',
                    'Content-Disposition' => 'inline; filename="generated_audio.wav"',
                    'Cache-Control' => 'no-cache',
                ]
            );
        } catch (\Exception $e) {
            return new Response('Erreur lors de la génération : ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
