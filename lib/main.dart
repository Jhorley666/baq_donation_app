import 'package:flutter/material.dart';
import 'views/home.dart';
import 'views/qr_gen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BAQ Donation App',
      home: const HomePage(),
      routes: {
        '/qr-generator': (context) => const QRGeneratorScreen(),
      },
    );
  }
}