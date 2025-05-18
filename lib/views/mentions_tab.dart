import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:confetti/confetti.dart';
import 'dart:math';

class MentionsTab extends StatefulWidget {
  const MentionsTab({super.key});

  @override
  State<MentionsTab> createState() => _MentionsTabState();
}

class _MentionsTabState extends State<MentionsTab> {
  final List<String> donators = [
    'Alice', 'Bob', 'Charlie', 'Diana', 'Evelyn', 'Frank', 'Grace', 'Henry'
  ];
  int currentDonator = 0;
  double randomX = 0;

  void _showNextDonator() {
    setState(() {
      currentDonator = (currentDonator + 1) % donators.length;
      randomX = (Random().nextDouble() * 0.6) - 0.3; // Range: -0.3 to 0.3 (as a fraction of width)
    });
  }

  @override
  void initState() {
    super.initState();
    randomX = (Random().nextDouble() * 0.6) - 0.3;
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        // Main content
        SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 24),
              const Text(
                'Mentions & Top Donators',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              SizedBox(
                height: 500,
                child: Center(
                  child: SizedBox(
                    width: double.infinity,
                    height: 400,
                    child: HeartDonationAnimation(
                      username: donators[currentDonator],
                      onAnimationComplete: _showNextDonator,
                      heartSize: 60,
                      textSize: 16,
                      randomX: randomX,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              // Social networks embedded posts (placeholder)
              const Text('Social Networks:', style: TextStyle(fontWeight: FontWeight.bold)),
              Container(
                height: 100,
                color: Colors.orange[50],
                child: const Center(child: Text('Embedded social post here')),
              ),
              const SizedBox(height: 24),
              // Notes and suggestions
              const Text('Notes & Suggestions:', style: TextStyle(fontWeight: FontWeight.bold)),
              TextFormField(
                maxLines: 3,
                decoration: const InputDecoration(
                  hintText: 'Leave your suggestion...',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              ElevatedButton.icon(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Suggestion sent!')),
                  );
                },
                icon: const Icon(Icons.send),
                label: const Text('Send'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Color(0xFFED6F1D),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(120, 40),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ],
    );
  }
}

class HeartDonationAnimation extends StatefulWidget {
  final String username;
  final VoidCallback? onAnimationComplete;
  final double heartSize;
  final double textSize;
  final double randomX;

  const HeartDonationAnimation({
    super.key,
    required this.username,
    this.onAnimationComplete,
    this.heartSize = 80,
    this.textSize = 22,
    this.randomX = 0,
  });

  @override
  State<HeartDonationAnimation> createState() => _HeartDonationAnimationState();
}

class _HeartDonationAnimationState extends State<HeartDonationAnimation>
    with TickerProviderStateMixin {
  late AnimationController _heartController;
  late AnimationController _confettiController;
  bool _showThanks = false;
  late ConfettiController _confetti;

  @override
  void initState() {
    super.initState();
    _heartController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    );
    _confettiController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );
    _confetti = ConfettiController(duration: const Duration(milliseconds: 800));

    _heartController.forward();
    _heartController.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        _confetti.play();
        _confettiController.forward();
        setState(() {
          _showThanks = true;
        });
        Future.delayed(const Duration(milliseconds: 1200), () {
          if (widget.onAnimationComplete != null) {
            widget.onAnimationComplete!();
          }
        });
      }
    });
  }

  @override
  void didUpdateWidget(covariant HeartDonationAnimation oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.username != widget.username || oldWidget.randomX != widget.randomX) {
      _heartController.reset();
      _confettiController.reset();
      _confetti.stop();
      setState(() {
        _showThanks = false;
      });
      _heartController.forward();
    }
  }

  @override
  void dispose() {
    _heartController.dispose();
    _confettiController.dispose();
    _confetti.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: widget.heartSize + 120,
      width: double.infinity,
      child: Stack(
        alignment: Alignment.bottomCenter,
        children: [
          AnimatedBuilder(
            animation: _heartController,
            builder: (context, child) {
              final value = _heartController.value;
              final double boxHeight = widget.heartSize + 120;
              final double startY = 0;
              final double endY = boxHeight / 2 - widget.heartSize / 2;
              final double translateY = startY + (endY - startY) * (1 - value);
              final double amplitude = 40;
              final double translateX = amplitude * sin(value * pi) + (widget.randomX * MediaQuery.of(context).size.width * 0.5);
              final double scale = 1.0 + 0.3 * value;
              final double opacity = value < 0.8 ? 1.0 : 1.0 - (value - 0.8) * 5;
              return Opacity(
                opacity: opacity.clamp(0.0, 1.0),
                child: Transform.translate(
                  offset: Offset(translateX, translateY - boxHeight / 2 + widget.heartSize / 2),
                  child: Transform.scale(
                    scale: scale,
                    child: Image.asset(
                      'assets/UI/heart.png',
                      width: widget.heartSize,
                      height: widget.heartSize,
                    ),
                  ),
                ),
              );
            },
          ),
          if (_showThanks)
            Positioned(
              bottom: widget.heartSize + 20,
              left: 0,
              right: 0,
              child: FadeTransition(
                opacity: _confettiController.drive(Tween(begin: 0.0, end: 1.0)),
                child: Container(
                  alignment: Alignment.center,
                  width: double.infinity,
                  child: Text(
                    'Thanks, @${widget.username}',
                    textAlign: TextAlign.center,
                    overflow: TextOverflow.ellipsis,
                    maxLines: 1,
                    style: TextStyle(
                      fontSize: widget.textSize,
                      fontWeight: FontWeight.bold,
                      color: Colors.pink,
                      shadows: const [
                        Shadow(
                          blurRadius: 8,
                          color: Colors.black26,
                          offset: Offset(0, 2),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          Positioned(
            bottom: widget.heartSize / 2,
            child: ConfettiWidget(
              confettiController: _confetti,
              blastDirectionality: BlastDirectionality.explosive,
              shouldLoop: false,
              colors: [Colors.pink, Colors.red, Colors.pinkAccent, Colors.redAccent],
              createParticlePath: _drawHeartPath,
              emissionFrequency: 0.8,
              numberOfParticles: 20,
              maxBlastForce: 15,
              minBlastForce: 8,
              gravity: 0.2,
            ),
          ),
        ],
      ),
    );
  }

  Path _drawHeartPath(Size size) {
    final path = Path();
    path.moveTo(0, 0);
    path.cubicTo(0, -10, 20, -10, 20, 0);
    path.cubicTo(20, 10, 0, 20, 0, 30);
    path.cubicTo(0, 20, -20, 10, -20, 0);
    path.cubicTo(-20, -10, 0, -10, 0, 0);
    return path;
  }
}