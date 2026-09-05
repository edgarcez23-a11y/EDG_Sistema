#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Conversor de PNG para ICO
Converte a logo do projeto para formato de ícone
"""

from PIL import Image
import os
import sys

def converter_png_para_ico(png_path, ico_path, tamanho=256):
    """
    Converte uma imagem PNG para ICO
    
    Args:
        png_path: Caminho do arquivo PNG
        ico_path: Caminho de saída do arquivo ICO
        tamanho: Tamanho da imagem (padrão 256x256)
    """
    try:
        # Abrir imagem
        img = Image.open(png_path)
        
        # Converter para RGB se necessário (ICO não suporta RGBA diretamente)
        if img.mode != 'RGBA':
            # Se for RGB ou outro formato, converter para RGBA para preservar qualidade
            img = img.convert('RGBA')
        
        # Criar múltiplos tamanhos para melhor qualidade
        tamanhos = [(16, 16), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)]
        
        # Redimensionar a imagem original para os tamanhos padrão
        imagens = []
        for tamanho in tamanhos:
            img_redimensionada = img.copy()
            img_redimensionada.thumbnail(tamanho, Image.Resampling.LANCZOS)
            
            # Criar imagem com fundo branco se necessário
            if img_redimensionada.mode == 'RGBA':
                imagens.append(img_redimensionada)
            else:
                imagens.append(img_redimensionada.convert('RGBA'))
        
        # Salvar como ICO
        img.save(ico_path, format='ICO', sizes=tamanhos)
        
        print(f"✓ Ícone criado com sucesso: {ico_path}")
        print(f"  Tamanhos inclusos: {tamanhos}")
        print(f"  Tamanho do arquivo: {os.path.getsize(ico_path) / 1024:.2f} KB")
        
        return True
        
    except ImportError:
        print("✗ ERRO: Pillow não está instalado")
        print("  Instale com: pip install Pillow")
        return False
    except FileNotFoundError:
        print(f"✗ ERRO: Arquivo não encontrado: {png_path}")
        return False
    except Exception as e:
        print(f"✗ ERRO: {str(e)}")
        return False

if __name__ == "__main__":
    # Caminhos
    script_dir = os.path.dirname(os.path.abspath(__file__))
    png_arquivo = os.path.join(script_dir, "public", "Logo Garcez gestão.png")
    ico_arquivo = os.path.join(script_dir, "oficina_icon3.ico")
    
    print("=" * 50)
    print("  Conversor PNG → ICO")
    print("=" * 50)
    print()
    
    if os.path.exists(png_arquivo):
        print(f"Convertendo: {png_arquivo}")
        print(f"Para: {ico_arquivo}")
        print()
        
        sucesso = converter_png_para_ico(png_arquivo, ico_arquivo)
        
        if sucesso:
            print()
            print("=" * 50)
            print("✓ Conversão concluída com sucesso!")
            print("=" * 50)
            sys.exit(0)
        else:
            sys.exit(1)
    else:
        print(f"✗ Logo não encontrada em: {png_arquivo}")
        sys.exit(1)
