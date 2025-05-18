import 'package:flutter/material.dart';

class HomePage extends StatelessWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('BAQ Donation App'),
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                'Welcome to BAQ Donation App',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 32),
              ElevatedButton.icon(
                icon: const Icon(Icons.login),
                label: const Text('Login'),
                onPressed: () {
                  // TODO: Implement login navigation
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Login pressed')),
                  );
                },
                style: ElevatedButton.styleFrom(
                  minimumSize: const Size(180, 48),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}